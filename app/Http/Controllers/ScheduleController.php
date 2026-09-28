<?php

namespace App\Http\Controllers;

use App\Enums\Permission;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Poly;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $this->denyUnless($request->user()->hasPermission(Permission::VIEW_SCHEDULE));

        $doctors = Doctor::query()
            ->with(['poly', 'schedules' => fn ($q) => $q->with('poly')->orderBy('start_time')])
            ->when($request->filled('poly_id'), fn ($q) => $q->where('poly_id', $request->integer('poly_id')))
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'ILIKE', '%'.$request->string('q')->toString().'%'))
            ->orderBy('name')
            ->get();

        return view('schedules.index', [
            'doctors' => $doctors,
            'polies' => Poly::orderBy('name')->get(),
            'days' => DoctorSchedule::DAYS,
            'orderedDays' => DoctorSchedule::ORDERED_DAYS,
            'canManage' => $request->user()->hasPermission(Permission::MANAGE_SCHEDULE),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_SCHEDULE));

        $data = $this->validateData($request);

        $exists = DoctorSchedule::where('doctor_id', $data['doctor_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Dokter tersebut sudah memiliki jadwal pada hari yang sama. Edit jadwal yang ada.');
        }

        DoctorSchedule::create($data);

        return back()->with('success', 'Jadwal praktik berhasil ditambahkan.');
    }

    public function update(Request $request, DoctorSchedule $schedule): RedirectResponse
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_SCHEDULE));

        $data = $this->validateData($request, $schedule);

        $clash = DoctorSchedule::where('doctor_id', $data['doctor_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->whereKeyNot($schedule->id)
            ->exists();

        if ($clash) {
            return back()->withInput()->with('error', 'Jadwal tersebut bertabrakan dengan jadwal lain pada hari yang sama.');
        }

        $schedule->update($data);

        return back()->with('success', 'Jadwal praktik berhasil diperbarui.');
    }

    public function destroy(DoctorSchedule $schedule): RedirectResponse
    {
        $this->denyUnless(auth()->user()->hasPermission(Permission::MANAGE_SCHEDULE));

        $schedule->delete();

        return back()->with('success', 'Jadwal praktik dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateData(Request $request, ?DoctorSchedule $schedule = null): array
    {
        $data = $request->validate([
            'doctor_id' => ['required', Rule::exists('doctors', 'id')],
            'poly_id' => ['nullable', Rule::exists('polies', 'id')],
            'day_of_week' => ['required', 'integer', 'between:1,7'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'slot_minutes' => ['required', 'integer', 'min:5', 'max:240'],
            'quota' => ['nullable', 'integer', 'min:1', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'end_time.after' => 'Waktu selesai harus lebih besar dari waktu mulai.',
            'day_of_week.between' => 'Hari tidak valid.',
        ], [
            'doctor_id' => 'dokter',
            'poly_id' => 'poli',
            'day_of_week' => 'hari',
            'start_time' => 'waktu mulai',
            'end_time' => 'waktu selesai',
            'slot_minutes' => 'durasi per pasien',
            'quota' => 'kuota pasien',
        ]);

        return $data + ['is_active' => $request->boolean('is_active')];
    }
}
