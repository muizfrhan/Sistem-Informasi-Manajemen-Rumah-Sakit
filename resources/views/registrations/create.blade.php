@extends('layouts.app')

@section('title', 'Pendaftaran Baru')
@section('heading', 'Pendaftaran Poli Baru')
@section('subheading', $patient ? $patient->name.' - '.$patient->mr_number : 'Pilih pasien dan poli tujuan')

@section('content')
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <form method="POST" action="{{ route('registrations.store') }}" class="lg:col-span-2">
            @csrf

            <div class="space-y-6">
                <x-card title="Pilih Pasien" subtitle="Cari berdasarkan No. RM, NIK, atau nama">
                    @if ($patient)
                        <input type="hidden" name="patient_id" value="{{ $patient->id }}">
                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg bg-teal-50 px-4 py-3 ring-1 ring-teal-200">
                            <div>
                                <p class="font-semibold text-slate-800">{{ $patient->name }}</p>
                                <p class="text-xs text-slate-600">{{ $patient->mr_number }} &middot; {{ $patient->gender->label() }} &middot; {{ $patient->age_years }} &middot; {{ $patient->insurance_type->label() }}</p>
                            </div>
                            <a href="{{ route('registrations.create') }}" class="text-xs font-medium text-teal-700 hover:underline">Ganti pasien</a>
                        </div>
                    @else
                        <div x-data="{ term: @js(old('q', '')) }">
                            <div class="flex gap-2">
                                <input type="search" name="q" x-model="term" placeholder="Ketik No. RM / NIK / nama, lalu tekan Enter"
                                       class="block w-full rounded-lg border-0 px-3 py-2 text-slate-900 shadow-sm ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset sm:text-sm ring-slate-300 focus:ring-teal-600">
                                <x-button type="submit" variant="secondary">Cari</x-button>
                            </div>
                        </div>

                        @php
                            $matches = \App\Models\Patient::query()->search(request('q'))->active()->limit(10)->get();
                        @endphp

                        @if (request()->filled('q'))
                            <div class="mt-4 space-y-2">
                                @forelse ($matches as $match)
                                    <label class="flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2.5 ring-1 ring-slate-200 hover:bg-slate-50">
                                        <input type="radio" name="patient_id" value="{{ $match->id }}" @checked(old('patient_id') == $match->id)
                                               class="h-4 w-4 border-slate-300 text-teal-600 focus:ring-teal-500">
                                        <span class="flex-1">
                                            <span class="block text-sm font-medium text-slate-800">{{ $match->name }}</span>
                                            <span class="block font-mono text-xs text-slate-500">{{ $match->mr_number }} &middot; {{ $match->gender->label() }} &middot; {{ $match->age }} th &middot; {{ $match->insurance_type->label() }}</span>
                                        </span>
                                    </label>
                                @empty
                                    <x-empty-state title="Pasien tidak ditemukan"
                                                   message="Tidak ada pasien yang cocok dengan pencarian tersebut.">
                                        <x-button :href="route('patients.create')" size="sm">+ Daftarkan Pasien Baru</x-button>
                                    </x-empty-state>
                                @endforelse
                            </div>
                        @endif
                    @endif
                    @error('patient_id')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror
                </x-card>

                @if ($patient)
                    <x-card title="Detail Kunjungan">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <x-select name="poly_id" label="Poli Tujuan" required
                                      :options="$polies->pluck('name', 'id')->all()"
                                      placeholder="Pilih poli tujuan" />

                            <div x-data="polyId: @js(old('poly_id'))" x-init="polyId = @js(old('poly_id'))">
                                <label for="doctor_id" class="mb-1.5 block text-sm font-medium text-slate-700">Dokter</label>
                                <select id="doctor_id" name="doctor_id"
                                        class="block w-full rounded-lg border-0 px-3 py-2 text-slate-900 shadow-sm ring-1 ring-inset sm:text-sm ring-slate-300 focus:ring-teal-600">
                                    <option value="">Otomatis (pilih dokter poli)</option>
                                    @foreach ($doctors as $doctor)
                                        <option value="{{ $doctor->id }}"
                                                data-poly="{{ $doctor->poly_id }}"
                                                @selected(old('doctor_id') == $doctor->id)>
                                            {{ $doctor->name }}{{ $doctor->poly ? ' - '.$doctor->poly->name : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1.5 text-xs text-slate-500">Jika dikosongkan, sistem memilih dokter aktif secara acak dari poli tujuan.</p>
                                @error('doctor_id')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>

                            <div class="sm:col-span-2">
                                <x-textarea name="complaint" label="Keluhan Utama" :value="old('complaint')"
                                            :rows="3" maxlength="255"
                                            placeholder="Contoh: Demam dan sakit kepala sejak tiga hari" />
                            </div>

                            <x-checkbox name="is_emergency" label="Pasien Gawat Darurat (UGD)"
                                        hint="Pasien gawat darurat diprioritaskan pada urutan antrean." />
                        </div>
                    </x-card>
                @endif

                <div class="flex justify-end">
                    <x-button type="submit" :disabled="! $patient">Daftarkan ke Antrean</x-button>
                </div>
            </div>
        </form>

        <div class="space-y-6">
            <x-card title="Alur Pendaftaran">
                <ol class="space-y-3 text-sm">
                    @foreach ([
                        'Pilih pasien yang akan mendaftar',
                        'Tentukan poli tujuan dan dokter',
                        'Catat keluhan utama pasien',
                        'Nomor antrean dibuat otomatis per poli per hari',
                    ] as $index => $step)
                        <li class="flex gap-3">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-teal-100 text-xs font-bold text-teal-700">{{ $index + 1 }}</span>
                            <span class="text-slate-600">{{ $step }}</span>
                        </li>
                    @endforeach
                </ol>
            </x-card>

            <x-card title="Catatan">
                <ul class="space-y-2 text-xs text-slate-600">
                    <li>Satu pasien hanya dapat mendaftar satu kali pada poli yang sama per hari.</li>
                    <li>Nomor antrean direset setiap hari, tetapi berurutan per poli.</li>
                    <li>Pendaftaran tidak dapat dihapus, hanya dibatalkan atau ditandai tidak hadir.</li>
                </ul>
            </x-card>
        </div>
    </div>
@endsection
