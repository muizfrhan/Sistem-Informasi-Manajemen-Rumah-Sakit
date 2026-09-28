<?php

namespace App\Http\Controllers;

use App\Enums\AdmissionStatus;
use App\Enums\Permission;
use App\Enums\RoomClass;
use App\Models\Doctor;
use App\Models\InpatientAdmission;
use App\Models\Patient;
use App\Models\Room;
use App\Services\InpatientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InpatientController extends Controller
{
    public function __construct(private readonly InpatientService $inpatient) {}

    public function index(Request $request): View
    {
        $this->denyUnless($request->user()->hasPermission(Permission::VIEW_INPATIENT));

        $filter = $request->string('status')->toString() ?: 'ADMITTED';

        $admissions = InpatientAdmission::query()
            ->with(['patient', 'room', 'doctor'])
            ->when($request->filled('room_id'), fn ($q) => $q->where('room_id', $request->integer('room_id')))
            ->when(in_array($filter, array_column(AdmissionStatus::cases(), 'value'), true), fn ($q) => $q->where('status', $filter))
            ->when($request->filled('q'), fn ($q) => $q->whereHas('patient', fn ($p) => $p->search($request->string('q')->toString())))
            ->latest('admitted_at')
            ->paginate(20)
            ->withQueryString();

        $rooms = Room::query()
            ->withCount('activeAdmissions')
            ->with(['activeAdmissions.patient:id,name,mr_number'])
            ->when($request->filled('class'), fn ($q) => $q->where('room_class', $request->string('class')))
            ->orderBy('room_class')
            ->orderBy('code')
            ->get();

        return view('inpatient.index', [
            'admissions' => $admissions,
            'rooms' => $rooms,
            'classes' => RoomClass::cases(),
            'statusFilter' => $filter,
            'summary' => $this->inpatient->summary(),
            'canManage' => $request->user()->hasPermission(Permission::MANAGE_INPATIENT),
            'doctors' => Doctor::active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_INPATIENT));

        $data = $request->validate([
            'patient_id' => ['required', Rule::exists('patients', 'id')->whereNull('deleted_at')],
            'room_id' => ['required', Rule::exists('rooms', 'id')],
            'bed_number' => ['required', 'string', 'max:10'],
            'doctor_id' => ['nullable', Rule::exists('doctors', 'id')],
            'registration_id' => ['nullable', Rule::exists('registrations', 'id')],
            'diagnosis' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [], [
            'patient_id' => 'pasien',
            'room_id' => 'kamar',
            'bed_number' => 'nomor tempat tidur',
            'doctor_id' => 'dokter penanggung jawab',
            'diagnosis' => 'diagnosa masuk',
        ]);

        try {
            $admission = $this->inpatient->admit(
                Patient::findOrFail($data['patient_id']),
                Room::findOrFail($data['room_id']),
                $data['bed_number'],
                ! empty($data['doctor_id']) ? Doctor::find($data['doctor_id']) : null,
                ! empty($data['registration_id']) ? (int) $data['registration_id'] : null,
                $data['diagnosis'] ?? null,
                $data['notes'] ?? null,
            );
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', "{$admission->patient->name} dirawat inap di kamar {$admission->room->code} bed {$admission->bed_number}.");
    }

    public function discharge(Request $request, InpatientAdmission $inpatientadmission): RedirectResponse
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_INPATIENT));

        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [], ['notes' => 'catatan']);

        try {
            $this->inpatient->discharge($inpatientadmission, $data['notes'] ?? null);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Perawatan inap {$inpatientadmission->admission_number} ditandai selesai.");
    }

    public function cancel(Request $request, InpatientAdmission $inpatientadmission): RedirectResponse
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_INPATIENT));

        try {
            $this->inpatient->cancel($inpatientadmission);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Penerimaan inap dibatalkan dan tempat tidur dikosongkan.');
    }
}
