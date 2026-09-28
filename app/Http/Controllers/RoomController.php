<?php

namespace App\Http\Controllers;

use App\Enums\GenderPolicy;
use App\Enums\Permission;
use App\Enums\RoomClass;
use App\Enums\RoomStatus;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(Request $request): View
    {
        $this->denyUnless($request->user()->hasPermission(Permission::VIEW_INPATIENT));

        $rooms = Room::query()
            ->withCount('activeAdmissions')
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'ILIKE', '%'.$request->string('q')->toString().'%')
                ->orWhere('code', 'ILIKE', '%'.$request->string('q')->toString().'%')))
            ->when($request->filled('class'), fn ($q) => $q->where('room_class', $request->string('class')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderBy('code')
            ->paginate(25)
            ->withQueryString();

        return view('rooms.index', [
            'rooms' => $rooms,
            'classes' => RoomClass::cases(),
            'statuses' => RoomStatus::cases(),
            'genderPolicies' => GenderPolicy::cases(),
            'canManage' => $request->user()->hasPermission(Permission::MANAGE_INPATIENT),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_INPATIENT));

        $room = Room::create($this->validateData($request));

        return back()->with('success', "Kamar {$room->code} berhasil ditambahkan.");
    }

    public function update(Request $request, Room $room): RedirectResponse
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_INPATIENT));

        $data = $this->validateData($request, $room);

        if ($data['capacity'] < $room->occupiedBeds()) {
            return back()->withInput()->with('error', "Kapasitas tidak boleh lebih kecil dari {$room->occupiedBeds()} tempat tidur yang sedang terisi.");
        }

        $room->update($data);

        return back()->with('success', "Kamar {$room->code} berhasil diperbarui.");
    }

    public function destroy(Room $room): RedirectResponse
    {
        $this->denyUnless(auth()->user()->hasPermission(Permission::MANAGE_INPATIENT));

        if ($room->activeAdmissions()->exists()) {
            return back()->with('error', "Kamar {$room->code} masih memiliki pasien dirawat. Pindahkan pasien terlebih dahulu.");
        }

        if ($room->admissions()->exists()) {
            return back()->with('error', "Kamar {$room->code} memiliki riwayat perawatan inap dan tidak dapat dihapus. Nonaktifkan sebagai pemeliharaan.");
        }

        $room->delete();

        return back()->with('success', "Kamar {$room->code} dihapus.");
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateData(Request $request, ?Room $room = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:20', 'alpha_dash', Rule::unique('rooms', 'code')->ignore($room?->id)],
            'name' => ['required', 'string', 'max:100'],
            'room_class' => ['required', Rule::enum(RoomClass::class)],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'status' => ['required', Rule::enum(RoomStatus::class)],
            'gender_policy' => ['required', Rule::enum(GenderPolicy::class)],
            'daily_rate' => ['required', 'integer', 'min:0', 'max:100000000'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [], [
            'code' => 'kode kamar',
            'name' => 'nama kamar',
            'room_class' => 'kelas kamar',
            'capacity' => 'kapasitas',
            'gender_policy' => 'kebijakan gender',
            'daily_rate' => 'tarif harian',
        ]);
    }
}
