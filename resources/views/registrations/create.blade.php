@extends('layouts.app')

@section('title', 'Pendaftaran Baru')
@section('heading', 'Pendaftaran Poli Baru')
@section('subheading', $patient ? $patient->name . ' - ' . $patient->mr_number : 'Pilih pasien dan poli tujuan')

@section('content')
    <div class="space-y-5">
        <x-page-header title="Pendaftaran Poli Baru"
                       description="Cari pasien, tentukan poli tujuan, lalu masukkan ke antrean.">
            <x-slot:actions>
                <x-button :href="route('queue.index')" variant="ghost" icon="ticket">Papan Antrean</x-button>
            </x-slot:actions>
        </x-page-header>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            <form method="POST" action="{{ route('registrations.store') }}" class="space-y-5 lg:col-span-2"
                  x-data="{ polyId: @js(old('poly_id')), doctorId: @js(old('doctor_id')) }"
                  x-init="
                      $watch('polyId', (value) => {
                          const option = $el.querySelector('#doctor_id option[value=' + JSON.stringify(doctorId) + ']');
                          if (option && option.dataset.poly !== String(value)) { doctorId = ''; }
                      });
                  ">
                @csrf

                {{-- ---------- Pemilihan pasien ---------- --}}
                <x-card title="1. Pilih Pasien" subtitle="Cari berdasarkan No. Rekam Medis, NIK, atau nama">
                    @if ($patient)
                        <input type="hidden" name="patient_id" value="{{ $patient->id }}">

                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-brand/25 bg-brand-soft px-4 py-3">
                            <div class="min-w-0">
                                <p class="truncate font-bold text-brand-ink">{{ $patient->name }}</p>
                                <p class="mt-0.5 text-xs text-brand-ink/80">
                                    <span class="font-mono">{{ $patient->mr_number }}</span>
                                    &middot; {{ $patient->gender->label() }}
                                    &middot; {{ $patient->age }} th
                                    &middot; {{ $patient->insurance_type->label() }}
                                </p>
                            </div>
                            <a href="{{ route('registrations.create') }}" class="btn btn-sm btn-secondary shrink-0">
                                Ganti
                            </a>
                        </div>
                    @else
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <div class="min-w-0 flex-1">
                                <label for="q" class="sr-only">Cari pasien</label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-ink-3">
                                        <x-icon name="search" class="h-4 w-4" />
                                    </span>
                                    <input id="q" name="q" type="search" value="{{ $search }}"
                                           placeholder="No. RM, NIK, atau nama pasien"
                                           class="field-control pl-9">
                                </div>
                            </div>
                            <x-button type="submit" variant="secondary" class="shrink-0">Cari</x-button>
                        </div>

                        @if ($search !== '')
                            @if ($matches->isEmpty())
                                @php
                                    // Pesan dirakit di PHP karena tanda kutip di dalam nilai
                                    // atribut komponen membuat Blade gagal mengompilasi tag.
                                    $notFoundMessage = 'Tidak ada pasien yang cocok dengan "'.$search.'".';
                                @endphp
                                <x-empty-state compact title="Pasien tidak ditemukan"
                                               :message="$notFoundMessage"
                                               icon="search">
                                    <x-button :href="route('patients.create')" size="sm" icon="user-plus">Daftarkan Pasien Baru</x-button>
                                </x-empty-state>
                            @else
                                <ul class="mt-4 space-y-2" role="radiogroup" aria-label="Hasil pencarian pasien">
                                    @foreach ($matches as $match)
                                        <li>
                                            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-line p-3 transition hover:border-brand/40 hover:bg-surface-2">
                                                <input type="radio" name="patient_id" value="{{ $match->id }}"
                                                       @checked(old('patient_id') == $match->id)
                                                       class="h-4 w-4 shrink-0 border-line-2 text-brand accent-brand focus:ring-2 focus:ring-brand">
                                                <span class="min-w-0 flex-1">
                                                    <span class="block truncate text-sm font-semibold text-ink">{{ $match->name }}</span>
                                                    <span class="mt-0.5 block font-mono text-xs text-ink-3">
                                                        {{ $match->mr_number }} &middot; {{ $match->gender->label() }}
                                                        &middot; {{ $match->age }} th &middot; {{ $match->insurance_type->label() }}
                                                    </span>
                                                </span>
                                            </label>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        @else
                            <div class="mt-4">
                                <x-empty-state compact icon="search"
                                               title="Cari pasien untuk memulai"
                                               message="Ketik No. Rekam Medis, NIK, atau nama pasien pada kolom di atas." />
                            </div>
                        @endif
                    @endif

                    @error('patient_id')
                        <p class="field-error"><x-icon name="exclamation" class="h-3.5 w-3.5 shrink-0" /><span>{{ $message }}</span></p>
                    @enderror
                </x-card>

                {{-- ---------- Detail kunjungan ---------- --}}
                @if ($patient)
                    <x-card title="2. Detail Kunjungan" subtitle="Poli tujuan, dokter, dan keluhan utama">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="poly_id" class="field-label">
                                    Poli Tujuan <span class="text-danger-ink" aria-hidden="true">*</span>
                                    <span class="sr-only">(wajib diisi)</span>
                                </label>
                                <select id="poly_id" name="poly_id" required x-model="polyId"
                                        @error('poly_id') aria-invalid="true" @enderror
                                        class="field-control @error('poly_id') field-control-error @enderror">
                                    <option value="">Pilih poli tujuan</option>
                                    @foreach ($polies as $poly)
                                        <option value="{{ $poly->id }}" @selected(old('poly_id') == $poly->id)>
                                            {{ $poly->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('poly_id')
                                    <p class="field-error"><x-icon name="exclamation" class="h-3.5 w-3.5 shrink-0" /><span>{{ $message }}</span></p>
                                @enderror
                            </div>

                            <div>
                                <label for="doctor_id" class="field-label">Dokter</label>
                                <select id="doctor_id" name="doctor_id" x-model="doctorId"
                                        class="field-control">
                                    <option value="">Otomatis (dokter aktif poli)</option>
                                    @foreach ($doctors as $doctor)
                                        <option value="{{ $doctor->id }}" data-poly="{{ $doctor->poly_id }}">
                                            {{ $doctor->name }}{{ $doctor->poly ? ' - ' . $doctor->poly->name : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="field-hint">Jika dikosongkan, sistem memilih dokter aktif secara acak dari poli tujuan.</p>
                                @error('doctor_id')
                                    <p class="field-error"><x-icon name="exclamation" class="h-3.5 w-3.5 shrink-0" /><span>{{ $message }}</span></p>
                                @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <x-textarea name="complaint" label="Keluhan Utama" :rows="3" maxlength="255"
                                            :value="old('complaint')"
                                            placeholder="Contoh: Demam dan sakit kepala sejak tiga hari lalu" />
                            </div>

                            <div class="sm:col-span-2">
                                <x-checkbox name="is_emergency"
                                            label="Pasien Gawat Darurat (UGD)"
                                            hint="Pasien gawat darurat diprioritaskan pada urutan antrean di poli tujuan." />
                            </div>
                        </div>
                    </x-card>

                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <x-button :href="route('registrations.index')" variant="ghost">Batal</x-button>
                        <x-button type="submit" icon="check" :disabled="! $patient">Daftarkan ke Antrean</x-button>
                    </div>
                @endif
            </form>

            {{-- ---------- Panel samping ---------- --}}
            <div class="space-y-4">
                <x-card title="Alur Pendaftaran">
                    <ol class="space-y-3">
                        @foreach ([
                            'Pilih pasien yang akan mendaftar',
                            'Tentukan poli tujuan dan dokter',
                            'Catat keluhan utama pasien',
                            'Nomor antrean dibuat otomatis per poli per hari',
                        ] as $index => $step)
                            <li class="flex gap-3 text-sm">
                                <span @class([
                                    'flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-bold',
                                    'bg-brand text-brand-fg' => $index < ($patient ? 2 : 0),
                                    'bg-surface-3 text-ink-3' => $index >= ($patient ? 2 : 0),
                                ])>{{ $index + 1 }}</span>
                                <span @class(['text-ink-2' => $index < ($patient ? 2 : 0), 'text-ink-3' => $index >= ($patient ? 2 : 0)])>
                                    {{ $step }}
                                </span>
                            </li>
                        @endforeach
                    </ol>
                </x-card>

                <x-card title="Aturan Pendaftaran">
                    <ul class="space-y-2 text-xs leading-relaxed text-ink-2">
                        <li class="flex gap-2">
                            <x-icon name="info" class="mt-px h-3.5 w-3.5 shrink-0 text-ink-3" />
                            <span>Satu pasien hanya dapat mendaftar satu kali pada poli yang sama per hari.</span>
                        </li>
                        <li class="flex gap-2">
                            <x-icon name="info" class="mt-px h-3.5 w-3.5 shrink-0 text-ink-3" />
                            <span>Pasien boleh mendaftar di poli berbeda pada hari yang sama.</span>
                        </li>
                        <li class="flex gap-2">
                            <x-icon name="info" class="mt-px h-3.5 w-3.5 shrink-0 text-ink-3" />
                            <span>Nomor antrean direset setiap hari, tetapi tetap berurutan per poli.</span>
                        </li>
                        <li class="flex gap-2">
                            <x-icon name="info" class="mt-px h-3.5 w-3.5 shrink-0 text-ink-3" />
                            <span>Pendaftaran tidak dapat dihapus, hanya dibatalkan atau ditandai tidak hadir.</span>
                        </li>
                    </ul>
                </x-card>
            </div>
        </div>
    </div>
@endsection
