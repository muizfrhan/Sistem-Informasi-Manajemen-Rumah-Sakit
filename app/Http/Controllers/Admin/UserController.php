<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Permission;
use App\Enums\RoleCode;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_USER));

        $users = User::query()
            ->with('role')
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'ILIKE', '%'.$request->string('q')->toString().'%')
                ->orWhere('username', 'ILIKE', '%'.$request->string('q')->toString().'%')))
            ->when($request->filled('role_id'), fn ($q) => $q->where('role_id', $request->integer('role_id')))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => Role::orderBy('sort_order')->get(),
        ]);
    }

    public function create(): View
    {
        $this->denyUnless(auth()->user()->hasPermission(Permission::MANAGE_USER));

        return view('admin.users.form', [
            'user' => new User,
            'roles' => Role::orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_USER));

        $data = $this->validateData($request);
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Pengguna {$user->name} berhasil dibuat.");
    }

    public function edit(User $user): View
    {
        $this->denyUnless(auth()->user()->hasPermission(Permission::MANAGE_USER));

        return view('admin.users.form', [
            'user' => $user,
            'roles' => Role::orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_USER));

        $data = $this->validateData($request, $user);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        if ($user->isSuperAdmin() && ($data['role_id'] ?? null) !== $user->role_id) {
            return back()->withInput()->with('error', 'Peran Super Admin tidak dapat diubah.');
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Pengguna {$user->name} berhasil diperbarui.");
    }

    public function toggle(User $user): RedirectResponse
    {
        $this->denyUnless(auth()->user()->hasPermission(Permission::MANAGE_USER));

        if ($user->is(self)) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        return back()->with('success', $user->is_active ? 'Akun diaktifkan.' : 'Akun dinonaktifkan.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->denyUnless(auth()->user()->hasPermission(Permission::MANAGE_USER));

        if ($user->is(auth()->user())) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return back()->with('success', 'Pengguna dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateData(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'role_id' => ['required', Rule::exists('roles', 'id')],
            'name' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($user?->id)],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => ['nullable', 'string', 'max:25'],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
        ], [
            'role_id' => 'peran',
            'name' => 'nama lengkap',
            'password' => 'kata sandi',
        ]) + [
            'is_active' => $request->boolean('is_active'),
            'email' => $request->filled('email') ? $request->string('email')->toString() : null,
        ];
    }
}
