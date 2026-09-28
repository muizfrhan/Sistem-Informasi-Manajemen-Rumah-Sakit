<?php

namespace App\Http\Controllers;

use App\Enums\DiagnosisType;
use App\Enums\MedicalRecordStatus;
use App\Enums\Permission;
use App\Models\Doctor;
use App\Models\Icd10Code;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Registration;
use App\Services\AuditLogger;
use App\Services\MedicalRecordService;
use App\Services\PharmacyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MedicalRecordController extends Controller
{
    public function __construct(
        private readonly MedicalRecordService $records,
        private readonly PharmacyService $pharmacy,
        private readonly AuditLogger $audit,
    ) {}

    public function index(Request $request): View
    {
        $this->denyUnless($request->user()->hasPermission(Permission::VIEW_EMR));

        $records = MedicalRecord::query()
            ->with(['patient', 'doctor', 'registration.poly'])
            ->when($request->filled('patient_id'), fn ($q) => $q->where('patient_id', $request->integer('patient_id')))
            ->when($request->filled('doctor_id'), fn ($q) => $q->where('doctor_id', $request->integer('doctor_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('type'), fn ($q) => $q->where('record_type', $request->string('type')))
            ->when($request->filled('q'), fn ($q) => $q->where('diagnosis', 'ILIKE', '%'.$request->string('q')->toString().'%'))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date('to')))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('medical-records.index', [
            'records' => $records,
            'statuses' => MedicalRecordStatus::cases(),
            'doctors' => Doctor::active()->orderBy('name')->get(),
            'patient' => $request->filled('patient_id') ? Patient::find($request->integer('patient_id')) : null,
        ]);
    }

    public function create(Request $request): View
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_EMR));

        $query = Registration::query()
            ->whereDoesntHave('medicalRecords')
            ->with(['patient', 'poly', 'doctor'])
            ->orderByDesc('visit_date');

        if ($request->filled('registration_id')) {
            $query->whereKey($request->integer('registration_id'));
        }

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->integer('patient_id'));
        }

        $candidates = $query->limit(100)->get();

        // Kunjungan tunggal: langsung buka editor tanpa halaman pemilihan.
        if ($candidates->count() === 1 && ! $request->filled('registration_id')) {
            return $this->editor($candidates->first());
        }

        return view('medical-records.create', [
            'candidates' => $candidates,
            'selectedPatient' => $request->filled('patient_id')
                ? Patient::find($request->integer('patient_id'))
                : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_EMR));

        $data = $this->validateRecord($request);

        try {
            $record = $this->records->create(
                Registration::findOrFail($data['registration_id']),
                $data,
                $this->collectDiagnoses($request),
                $this->collectProcedures($request),
            );
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('medical-records.show', $record)
            ->with('success', 'Rekam medis berhasil disimpan sebagai draft.');
    }

    public function show(MedicalRecord $medicalrecord): View
    {
        $this->denyUnless(auth()->user()->hasPermission(Permission::VIEW_EMR));

        $medicalrecord->load([
            'patient', 'doctor', 'author',
            'registration.poly',
            'diagnoses.icd10Code',
            'procedures',
            'prescriptions.items.drug',
        ]);

        $this->audit->viewed($medicalrecord::class, $medicalrecord->id);

        return view('medical-records.show', ['record' => $medicalrecord]);
    }

    public function edit(MedicalRecord $medicalrecord): RedirectResponse|View
    {
        $this->denyUnless(auth()->user()->hasPermission(Permission::MANAGE_EMR));

        if (! $medicalrecord->status->isEditable()) {
            return redirect()
                ->route('medical-records.show', $medicalrecord)
                ->with('error', 'Rekam medis yang sudah difinalisasi tidak dapat diubah.');
        }

        return $this->editor($medicalrecord);
    }

    public function update(Request $request, MedicalRecord $medicalrecord): RedirectResponse
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_EMR));

        $data = $this->validateRecord($request, $medicalrecord);

        try {
            $this->records->update(
                $medicalrecord,
                $data,
                $this->collectDiagnoses($request),
                $this->collectProcedures($request),
            );
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('medical-records.show', $medicalrecord)
            ->with('success', 'Rekam medis berhasil diperbarui.');
    }

    public function finalize(MedicalRecord $medicalrecord): RedirectResponse
    {
        $this->denyUnless(auth()->user()->hasPermission(Permission::MANAGE_EMR));

        try {
            $this->records->finalize($medicalrecord);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Rekam medis difinalisasi dan tidak dapat diubah lagi.');
    }

    public function storePrescription(Request $request, MedicalRecord $medicalrecord): RedirectResponse
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_EMR));

        if (! $medicalrecord->status->isEditable()) {
            return back()->with('error', 'Resep tidak dapat ditambahkan pada rekam medis yang sudah difinalisasi.');
        }

        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.drug_id' => ['required', 'integer', 'exists:drugs,id'],
            'items.*.dosage' => ['nullable', 'string', 'max:100'],
            'items.*.route' => ['nullable', 'string', 'max:60'],
            'items.*.frequency' => ['nullable', 'string', 'max:100'],
            'items.*.duration' => ['nullable', 'string', 'max:100'],
            'items.*.instructions' => ['nullable', 'string', 'max:500'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:10000'],
        ], [], [
            'items' => 'item resep',
            'items.*.drug_id' => 'obat',
            'items.*.quantity' => 'jumlah',
        ]);

        $this->pharmacy->createForRecord($medicalrecord, $data['items'], $data['notes'] ?? null);

        return back()->with('success', 'Resep berhasil dibuat dan menunggu verifikasi apoteker.');
    }

    protected function editor(MedicalRecord|Registration $subject): View
    {
        $record = $subject instanceof MedicalRecord ? $subject : null;
        $registration = $subject instanceof Registration ? $subject : $subject->registration;

        return view('medical-records.form', [
            'record' => $record,
            'registration' => $registration,
            'doctors' => $this->records->availableDoctors($registration?->poly_id),
            'icd10Codes' => Icd10Code::active()->orderBy('code')->get(),
            'diagnosisTypes' => DiagnosisType::cases(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateRecord(Request $request, ?MedicalRecord $record = null): array
    {
        return $request->validate([
            'registration_id' => [
                'required',
                Rule::exists('registrations', 'id')->whereNull('deleted_at'),
                Rule::unique('medical_records', 'registration_id')->ignore($record?->id),
            ],
            'doctor_id' => ['required', Rule::exists('doctors', 'id')],
            'chief_complaint' => ['nullable', 'string', 'max:255'],
            'anamnesis' => ['nullable', 'string', 'max:5000'],
            'physical_exam' => ['nullable', 'string', 'max:5000'],
            'diagnosis' => ['nullable', 'string', 'max:5000'],
            'treatment_plan' => ['nullable', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'blood_pressure' => ['nullable', 'string', 'max:20'],
            'pulse' => ['nullable', 'integer', 'min:0', 'max:300'],
            'temperature' => ['nullable', 'numeric', 'min:30', 'max:45'],
            'respiratory_rate' => ['nullable', 'integer', 'min:0', 'max:100'],
            'weight' => ['nullable', 'numeric', 'min:0.5', 'max:500'],
            'height' => ['nullable', 'numeric', 'min:20', 'max:260'],
        ], [
            'registration_id.unique' => 'Rekam medis untuk kunjungan ini sudah dibuat.',
            'temperature.min' => 'Suhu badan minimal 30 °C.',
            'temperature.max' => 'Suhu badan maksimal 45 °C.',
        ], [
            'registration_id' => 'kunjungan',
            'doctor_id' => 'dokter',
            'chief_complaint' => 'keluhan utama',
            'anamnesis' => 'anamnesis',
            'physical_exam' => 'pemeriksaan fisik',
            'treatment_plan' => 'rencana terapi',
            'blood_pressure' => 'tekanan darah',
            'pulse' => 'nadi',
            'temperature' => 'suhu badan',
            'respiratory_rate' => 'frekuensi napas',
            'weight' => 'berat badan',
            'height' => 'tinggi badan',
        ]);
    }

    /** @return list<array{icd10_code_id:int, diagnosis_type:string, description:string|null}> */
    protected function collectDiagnoses(Request $request): array
    {
        $rows = $request->input('diagnoses', []);

        if (! is_array($rows)) {
            return [];
        }

        $validCodes = Icd10Code::pluck('id');

        return collect($rows)
            ->filter(fn ($row) => ! empty($row['icd10_code_id']) && $validCodes->contains($row['icd10_code_id']))
            ->map(fn ($row) => [
                'icd10_code_id' => (int) $row['icd10_code_id'],
                'diagnosis_type' => $row['diagnosis_type'] ?? 'PRIMARY',
                'description' => $row['description'] ?? null,
            ])
            ->values()
            ->all();
    }

    /** @return list<array{name:string, code:string|null, fee:int, notes:string|null}> */
    protected function collectProcedures(Request $request): array
    {
        $rows = $request->input('procedures', []);

        if (! is_array($rows)) {
            return [];
        }

        return collect($rows)
            ->filter(fn ($row) => ! blank($row['name'] ?? null))
            ->map(fn ($row) => [
                'name' => (string) $row['name'],
                'code' => $row['code'] ?? null,
                'fee' => max(0, (int) ($row['fee'] ?? 0)),
                'notes' => $row['notes'] ?? null,
            ])
            ->values()
            ->all();
    }
}
