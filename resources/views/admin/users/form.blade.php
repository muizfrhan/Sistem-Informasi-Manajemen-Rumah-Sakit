@extends('layouts.app')

@section('title', $user->exists ? 'Ubah Pengguna' : 'Tambah Pengguna')
@section('heading', $user->exists ? 'Ubah Pengguna' : 'Tambah Pengguna Baru')
@section('subheading', $user->exists ? $user->name.' - '.($user->role?->name ?? 'Tanpa peran') : 'Tentukan peran dan hak akses')

@php
    $editing = $user->exists;
    $action = $editing ? route('admin.users.update', $user) : route('admin.users.store');

    $pageTitle = $editing ? 'Ubah Pengguna' : 'Tambah Pengguna Baru';
    $pageDescription = $editing
        ? 'Perbarui identitas, peran, dan keaktifan akun '.$user->name.'.'
        : 'Lengkapi identitas akun dan tentukan peran yang sesuai dengan tanggung jawab pengguna.';

    // Opsi peran dikirim ke Alpine sebagai peta id => nama. Nama field pada
    // form tetap "role_id" agar sesuai aturan validasi di backend.
    $roleDetails = $roles->mapWithKeys(fn ($role) => [
        (string) $role->id => [
            'name' => $role->name,
            'description' => $role->description,
            'permissions' => $role->permissions()->pluck('name')->all(),
        ],
    ])->all();

    $currentRoleId = (string) old('role_id', $user->role_id ?? '');
@endphp

@section('content')
    <div class="space-y-5">
        <x-page-header :title="$pageTitle" :description="$pageDescription">
            <x-slot:actions>
                <x-button :href="route('admin.users.index')" variant="secondary" icon="arrow-left">Kembali</x-button>
            </x-slot:actions>
        </x-page-header>

        <form method="POST" action="{{ $action }}" class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="space-y-5 lg:col-span-2">
                <x-card title="Informasi Akun" subtitle="Data identitas untuk masuk ke sistem">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-input name="name" label="Nama Lengkap" required :maxlength="150" class="sm:col-span-2"
                                 :value="old('name', $user->name)"
                                 placeholder="Nama lengkap sesuai dokumen resmi"
                                 autocomplete="name" />

                        <x-input name="username" label="Username" required :maxlength="50" autocomplete="username"
                                 :value="old('username', $user->username)"
                                 placeholder="Tanpa spasi"
                                 hint="Tanpa spasi, hanya huruf, angka, tanda hubung, dan garis bawah."
                                 x-on:input="$el.value = $el.value.replace(/\s/g, '')" />

                        <x-input name="email" label="Email" type="email" :maxlength="150" autocomplete="email"
                                 :value="old('email', $user->email)"
                                 placeholder="nama@contoh.id"
                                 hint="Alamat email harus unik bila diisi." />

                        <x-input name="phone" label="Nomor Telepon" :maxlength="25" inputmode="tel" autocomplete="tel"
                                 :value="old('phone', $user->phone)"
                                 placeholder="08xxxxxxxxxx" />

                        <x-checkbox name="is_active" label="Akun Aktif"
                                    hint="Akun nonaktif tidak dapat masuk ke sistem."
                                    :value="old('is_active', $editing ? $user->is_active : true)" />
                    </div>
                </x-card>

                <x-card title="Kata Sandi"
                       subtitle="Minimal 8 karakter. Kosongkan kedua kolom bila tidak ingin mengubah kata sandi.">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-input name="password" label="Kata Sandi" type="password" :required="! $editing"
                                 :minlength="$editing ? 8 : null" autocomplete="new-password"
                                 :hint="$editing ? 'Kosongkan bila tidak ingin mengubah kata sandi.' : 'Minimal 8 karakter.'" />

                        <x-input name="password_confirmation" label="Ulangi Kata Sandi" type="password"
                                 :required="! $editing" :minlength="$editing ? 8 : null" autocomplete="new-password" />
                    </div>
                </x-card>

                <div class="flex flex-wrap items-center justify-end gap-2">
                    <x-button :href="route('admin.users.index')" variant="ghost">Batal</x-button>
                    <x-button type="submit" icon="check">
                        {{ $editing ? 'Simpan Perubahan' : 'Buat Pengguna' }}
                    </x-button>
                </div>
            </div>

            <div class="space-y-4">
                {{-- Peran memakai id eksplisit agar tidak bentrok dengan
                     kontrol lain yang bernama "role_id" pada halaman lain. --}}
                <x-card title="Peran dan Hak Akses" subtitle="Peran menentukan modul yang dapat diakses pengguna"
                        x-data="{ selected: @js($currentRoleId), roles: @js($roleDetails) }">
                    <div>
                        <label for="user_role_id" class="field-label">
                            Peran <span class="text-danger-ink" aria-hidden="true">*</span><span class="sr-only">(wajib diisi)</span>
                        </label>
                        <select id="user_role_id" name="role_id" required
                                x-model="selected"
                                @error('role_id') aria-invalid="true" @enderror
                                class="field-control @error('role_id') field-control-error @enderror">
                            <option value="">Pilih peran</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" @selected((string) $role->id === $currentRoleId)>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('role_id')
                            <p class="field-error"><x-icon name="exclamation" class="h-3.5 w-3.5 shrink-0" /><span>{{ $message }}</span></p>
                        @enderror
                    </div>

                    <div class="mt-4 rounded-lg bg-surface-2 p-4">
                        <template x-if="roles[selected]">
                            <div>
                                <p class="text-sm font-semibold text-ink" x-text="roles[selected].name"></p>
                                <p class="mt-0.5 text-xs leading-relaxed text-ink-3" x-text="roles[selected].description"></p>

                                <div class="mt-3">
                                    <p class="text-[11px] font-semibold tracking-wide text-ink-3 uppercase">Hak Akses</p>
                                    <div class="mt-1.5 flex flex-wrap gap-1.5">
                                        <template x-for="permission in (roles[selected].permissions ?? [])" :key="permission">
                                            <span class="rounded-full bg-brand-soft px-2 py-0.5 text-[11px] font-medium text-brand-ink ring-1 ring-brand-ink/20 ring-inset"
                                                  x-text="permission"></span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <p x-show="! roles[selected]" x-cloak class="text-xs text-ink-3">
                            Pilih peran untuk melihat rincian hak aksesnya.
                        </p>
                    </div>
                </x-card>

                <x-card title="Peran yang Tersedia">
                    <ul class="space-y-3 text-xs">
                        @foreach (\App\Enums\RoleCode::cases() as $roleCode)
                            <li>
                                <p class="font-semibold text-ink">{{ $roleCode->label() }}</p>
                                <p class="mt-0.5 leading-relaxed text-ink-3">{{ $roleCode->description() }}</p>
                            </li>
                        @endforeach
                    </ul>
                </x-card>
            </div>
        </form>
    </div>
@endsection
