<?php

namespace App\Http\Controllers\Master;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Poly;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DoctorController extends Controller
{
    public function index(Request $request): View
    {
        $this->denyUnless($request->user()->hasPermission(Permission::VIEW_MASTER));

        $doctors = Doctor::query()
            ->with('poly')
            ->when($request->filled('poly_id'), fn ($q) => $q->where('poly_id', $request->integer('poly_id')))
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'ILIKE', '%'.$request->string('q')->toString().'%'))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('master.doctors.index', [
            'doctors' => $doctors,
            'polies' => Poly::orderBy('name')->get(),
            'canManage' => $request->user()->hasPermission(Permission::MANAGE_MASTER),
        ]);
    }

    public function create(Request $request): View
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_MASTER));

        return view('master.doctors.form', [
            'doctor' => new Doctor,
            'polies' => Poly::orderBy('name')->get(),
            'users' => User::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_MASTER));

        $data = $this->validateData($request);
        $data['code'] ??= $this->generateCode($data['name']);

        $doctor = Doctor::create($data);

        return redirect()
            ->route('master.doctors.index')
            ->with('success', "Dokter {$doctor->name} berhasil ditambahkan.");
    }

    public function edit(Request $request, Doctor $doctor): View
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_MASTER));

        return view('master.doctors.form', [
            'doctor' => $doctor,
            'polies' => Poly::orderBy('name')->get(),
            'users' => User::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Doctor $doctor): RedirectResponse
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_MASTER));

        $doctor->update($this->validateData($request, $doctor));

        return redirect()
            ->route('master.doctors.index')
            ->with('success', "Dokter {$doctor->name} berhasil diperbarui.");
    }

    public function destroy(Doctor $doctor): RedirectResponse
    {
        $this->denyUnless(auth()->user()->hasPermission(Permission::MANAGE_MASTER));

        if ($doctor->registrations()->exists()) {
            return back()->with('error', 'Dokter tidak dapat dihapus karena memiliki riwayat pasien. Nonaktifkan saja.');
        }

        $doctor->delete();

        return back()->with('success', 'Data dokter dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateData(Request $request, ?Doctor $doctor = null): array
    {
        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:20', 'alpha_dash', Rule::unique('doctors', 'code')->ignore($doctor?->id)],
            'name' => ['required', 'string', 'max:150'],
            'nip' => ['nullable', 'string', 'max:30'],
            'specialty' => ['nullable', 'string', 'max:100'],
            'sip_number' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:25'],
            'poly_id' => ['nullable', Rule::exists('polies', 'id')],
            'user_id' => ['nullable', Rule::exists('users', 'id')],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'code.unique' => 'Kode dokter sudah digunakan.',
        ], [
            'name' => 'nama dokter',
            'nip' => 'NIP',
            'poly_id' => 'poli',
        ]);

        return $data + ['is_active' => $request->boolean('is_active')];
    }

    protected function generateCode(string $name): string
    {
        $initials = collect(preg_split('/\s+/', trim($name)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn ($p) => Str::upper(Str::substr($p, 0, 1)))
            ->implode('');

        $code = ($initials ?: 'DR').Str::upper(Str::random(3));

        while (Doctor::where('code', $code)->exists()) {
            $code = ($initials ?: 'DR').Str::upper(Str::random(3));
        }

        return $code;
    }
}
