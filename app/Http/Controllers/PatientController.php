<?php

namespace App\Http\Controllers;

use App\Enums\Gender;
use App\Enums\InsuranceType;
use App\Enums\Permission;
use App\Models\Patient;
use App\Services\AuditLogger;
use App\Services\PatientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function __construct(
        private readonly PatientService $patients,
        private readonly AuditLogger $audit,
    ) {}

    public function index(Request $request): View
    {
        $this->denyUnless($request->user()->hasPermission(Permission::VIEW_PATIENT));

        $patients = Patient::query()
            ->search($request->string('q')->toString())
            ->when($request->filled('insurance_type'), fn ($q) => $q->where('insurance_type', $request->string('insurance_type')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->string('status') === 'active'))
            ->withCount(['registrations', 'medicalRecords', 'invoices', 'admissions'])
            ->orderBy('name')
            ->paginate((int) config('simrs.per_page', 20))
            ->withQueryString();

        return view('patients.index', [
            'patients' => $patients,
            'search' => $request->string('q')->toString(),
            'insuranceTypes' => InsuranceType::cases(),
        ]);
    }

    public function create(): View
    {
        $this->denyUnless(auth()->user()->hasPermission(Permission::MANAGE_PATIENT));

        return view('patients.create', [
            'insuranceTypes' => InsuranceType::cases(),
            'genders' => Gender::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_PATIENT));

        $data = $this->validatePatient($request);

        try {
            $patient = $this->patients->create($data);
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('patients.show', $patient)
            ->with('success', "Pasien berhasil didaftarkan dengan No. Rekam Medis {$patient->mr_number}.");
    }

    public function show(Patient $patient): View
    {
        $this->denyUnless(auth()->user()->hasPermission(Permission::VIEW_PATIENT));

        $patient->load([
            'creator',
            'registrations' => fn ($q) => $q->with(['poly', 'doctor'])->latest('visit_date')->limit(20),
            'medicalRecords' => fn ($q) => $q
                ->with(['doctor', 'registration.poly', 'diagnoses.icd10Code'])
                ->latest('created_at')
                ->limit(20),
            'prescriptions' => fn ($q) => $q->with(['doctor', 'items'])->latest('id')->limit(20),
            'invoices' => fn ($q) => $q->latest('issued_at')->limit(20),
            'admissions' => fn ($q) => $q->with('room')->latest('admitted_at')->limit(20),
        ]);

        $patient->loadCount(['registrations', 'medicalRecords', 'invoices', 'admissions']);

        $this->audit->viewed($patient::class, $patient->id);

        return view('patients.show', [
            'patient' => $patient,
            'canRegister' => auth()->user()->hasPermission(Permission::MANAGE_REGISTRATION),
            'canWriteEmr' => auth()->user()->hasPermission(Permission::MANAGE_EMR),
        ]);
    }

    public function edit(Patient $patient): View
    {
        $this->denyUnless(auth()->user()->hasPermission(Permission::MANAGE_PATIENT));

        return view('patients.edit', [
            'patient' => $patient,
            'insuranceTypes' => InsuranceType::cases(),
            'genders' => Gender::cases(),
        ]);
    }

    public function update(Request $request, Patient $patient): RedirectResponse
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_PATIENT));

        $data = $this->validatePatient($request, $patient);

        try {
            $this->patients->update($patient, $data);
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('patients.show', $patient)
            ->with('success', 'Data pasien berhasil diperbarui.');
    }

    public function toggle(Patient $patient): RedirectResponse
    {
        $this->denyUnless(auth()->user()->hasPermission(Permission::MANAGE_PATIENT));

        $patient = $this->patients->toggleActive($patient);

        return back()->with(
            'success',
            $patient->is_active ? 'Pasien diaktifkan kembali.' : 'Pasien dinonaktifkan.'
        );
    }

    public function destroy(Patient $patient): RedirectResponse
    {
        $this->denyUnless(auth()->user()->hasPermission(Permission::MANAGE_PATIENT));

        if ($patient->registrations()->exists()) {
            return back()->with('error', 'Pasien tidak dapat dihapus karena memiliki riwayat pendaftaran. Nonaktifkan saja.');
        }

        $patient->delete();

        return redirect()
            ->route('patients.index')
            ->with('success', 'Data pasien dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatePatient(Request $request, ?Patient $patient = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'nik' => ['required', 'string', 'max:20', 'regex:/^\d{16}$/'],
            'gender' => ['required', Rule::enum(Gender::class)],
            'birth_date' => ['required', 'date', 'before:today', 'after:1900-01-01'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'blood_type' => ['nullable', Rule::in(['A', 'B', 'AB', 'O', '-'])],
            'marital_status' => ['nullable', Rule::in(['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati'])],
            'phone' => ['nullable', 'string', 'max:25', 'regex:/^[0-9+\-\s()]+$/'],
            'address' => ['nullable', 'string', 'max:1000'],
            'village' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'insurance_type' => ['required', Rule::enum(InsuranceType::class)],
            'insurance_number' => ['nullable', 'string', 'max:100'],
            'insurance_name' => ['nullable', 'string', 'max:150'],
            'emergency_contact_name' => ['nullable', 'string', 'max:150'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:25'],
        ], [
            'nik.regex' => 'NIK harus terdiri dari tepat 16 digit angka.',
            'birth_date.before' => 'Tanggal lahir tidak boleh di masa depan.',
        ], [
            'name' => 'nama lengkap',
            'birth_date' => 'tanggal lahir',
        ]);

        if ($data['insurance_type'] === InsuranceType::NONE->value) {
            $data['insurance_number'] = null;
            $data['insurance_name'] = null;
        } elseif (empty($data['insurance_number'])) {
            $request->validate([
                'insurance_number' => ['required', 'string', 'max:100'],
            ], [], ['insurance_number' => 'nomor BPJS/asuransi']);
        }

        return $data;
    }
}
