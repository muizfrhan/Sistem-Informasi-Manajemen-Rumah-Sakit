<?php

namespace App\Http\Controllers;

use App\Enums\Permission;
use App\Enums\RegistrationStatus;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Poly;
use App\Models\Registration;
use App\Services\AuditLogger;
use App\Services\QueueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function __construct(
        private readonly QueueService $queue,
        private readonly AuditLogger $audit,
    ) {}

    public function index(Request $request): View
    {
        $this->denyUnless($request->user()->hasPermission(Permission::VIEW_REGISTRATION));

        $registrations = Registration::query()
            ->when($request->filled('poly_id'), fn ($q) => $q->where('poly_id', $request->integer('poly_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('date'), fn ($q) => $q->whereDate('visit_date', $request->date('date')))
            ->with(['patient', 'poly', 'doctor'])
            ->latest('visit_date')
            ->orderBy('queue_number')
            ->paginate(25)
            ->withQueryString();

        return view('registrations.index', [
            'registrations' => $registrations,
            'polies' => Poly::active()->orderBy('name')->get(),
            'statuses' => RegistrationStatus::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_REGISTRATION));

        $patient = null;

        if ($request->filled('patient_id')) {
            $patient = Patient::findOrFail($request->integer('patient_id'));
        }

        $search = $request->string('q')->toString();
        $matches = $patient
            ? collect()
            : Patient::query()
                ->search($search)
                ->active()
                ->orderBy('name')
                ->limit(10)
                ->get();

        return view('registrations.create', [
            'patient' => $patient,
            'matches' => $matches,
            'search' => $search,
            'polies' => Poly::active()->orderBy('name')->get(),
            'doctors' => Doctor::active()->with('poly')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_REGISTRATION));

        $data = $request->validate([
            'patient_id' => ['required', Rule::exists('patients', 'id')->whereNull('deleted_at')],
            'poly_id' => ['required', Rule::exists('polies', 'id')->where('is_active', true)],
            'doctor_id' => ['nullable', Rule::exists('doctors', 'id')],
            'complaint' => ['nullable', 'string', 'max:255'],
            'is_emergency' => ['nullable', 'boolean'],
        ], [], [
            'patient_id' => 'pasien',
            'poly_id' => 'poli tujuan',
            'doctor_id' => 'dokter',
        ]);

        $patient = Patient::findOrFail($data['patient_id']);
        $poly = Poly::findOrFail($data['poly_id']);

        $doctor = ! empty($data['doctor_id'])
            ? Doctor::find($data['doctor_id'])
            : $this->queue->pickDoctor($poly);

        try {
            $registration = $this->queue->register(
                $patient,
                $poly,
                $doctor,
                $data['complaint'] ?? null,
                (bool) ($data['is_emergency'] ?? false)
            );
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('queue.index')
            ->with('success', "Pendaftaran berhasil. No. Antrean {$registration->queue_code} untuk {$patient->name}.");
    }

    public function show(Registration $registration): View
    {
        $this->denyUnless(auth()->user()->hasPermission(Permission::VIEW_REGISTRATION));

        $registration->load([
            'patient', 'poly', 'doctor', 'registrar',
            'medicalRecords' => fn ($q) => $q->with(['doctor', 'diagnoses.icd10Code'])->latest('id'),
        ]);

        $this->audit->viewed($registration::class, $registration->id);

        return view('registrations.show', ['registration' => $registration]);
    }
}
