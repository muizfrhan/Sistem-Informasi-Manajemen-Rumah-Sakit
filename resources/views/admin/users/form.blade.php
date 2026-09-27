@extends('layouts.app')

@section('title', $user->exists ? 'Ubah Pengguna' : 'Tambah Pengguna')
@section('heading', $user->exists ? 'Ubah Pengguna' : 'Tambah Pengguna Baru')
@section('subheading', $user->exists ? $user->name.' - '.$user->role?->name : 'Tentukan peran dan hak akses')

@section('content')
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <form method="POST"
              action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}"
              class="space-y-6 lg:col-span-2">
            @csrf
            @if ($user->exists) @method('PUT') @endif

            <x-card title="Informasi Akun">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-input name="name" label="Nama Lengkap" :value="$user->name" required maxlength="150" class="sm:col-span-2" />
                    <x-input name="username" label="Username" :value="$user->username" required maxlength="50"
                             hint="Tanpa spasi, hanya huruf dan angka" oninput="this.value=this.value.replace(/\s/g,'')" />
                    <x-input name="email" label="Email" type="email" :value="$user->email" maxlength="150" />
                    <x-input name="phone" label="Telepon" :value="$user->phone" maxlength="25" />

                    <x-input name="password" label="Kata Sandi" type="password" :required="! $user->exists" minlength="8"
                             :hint="$user->exists ? 'Kosongkan bila tidak ingin mengubah kata sandi' : 'Minimal 8 karakter'" />
                    <x-input name="password_confirmation" label="Ulangi Kata Sandi" type="password" :required="! $user->exists" minlength="8" />
                </div>
            </x-card>

            <x-card title="Peran & Hak Akses">
                <div class="space-y-4">
                    <x-select name="role_id" label="Peran" required
                              :options="$roles->pluck('name', 'id')->all()" placeholder="Pilih peran"
                              x-data="{}" x-on:change="$dispatch('role-changed')" id="role-select" />

                    <div x-data="{
                            selected: @js(old('role_id', $user->role_id)),
                            roles: @js($roles->mapWithKeys(fn ($r) => [$r->id => ['name' => $r->name, 'description' => $r->description, 'permissions' => $r->permissions->pluck('name')]])->all())
                         }"
                         class="rounded-lg bg-slate-50 p-4">
                        <template x-for="role in [roles[selected]]" :key="selected">
                            <div>
                                <p class="text-sm font-semibold text-slate-800" x-text="role?.name"></p>
                                <p class="mt-0.5 text-xs text-slate-500" x-text="role?.description"></p>
                                <div class="mt-3">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Hak Akses</p>
                                    <div class="mt-1.5 flex flex-wrap gap-1.5">
                                        <template x-for="permission in (role?.permissions ?? [])" :key="permission">
                                            <span class="rounded-full bg-white px-2 py-0.5 text-[11px] text-slate-600 ring-1 ring-slate-200" x-text="permission"></span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <x-checkbox name="is_active" label="Akun Aktif" :value="$user->exists ? $user->is_active : true"
                                hint="Akun nonaktif tidak dapat masuk ke sistem" />
                </div>
            </x-card>

            <div class="flex justify-end gap-3">
                <x-button :href="route('admin.users.index')" variant="secondary">Batal</x-button>
                <x-button type="submit">{{ $user->exists ? 'Simpan Perubahan' : 'Buat Pengguna' }}</x-button>
            </div>
        </form>

        <div class="space-y-6">
            <x-card title="Peran yang Tersedia">
                <ul class="space-y-2.5 text-xs">
                    @foreach (\App\Enums\RoleCode::cases() as $roleCode)
                        <li>
                            <p class="font-semibold text-slate-700">{{ $roleCode->label() }}</p>
                            <p class="text-slate-500">{{ $roleCode->description() }}</p>
                        </li>
                    @endforeach
                </ul>
            </x-card>
        </div>
    </div>
@endsection
