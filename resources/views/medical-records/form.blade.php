@extends('layouts.app')

@section('title', $record ? 'Ubah Rekam Medis' : 'Rekam Medis Baru')
@section('heading', $record ? 'Ubah Rekam Medis' : 'Rekam Medis Baru')
@section('subheading', $registration->patient->name . ' - ' . $registration->poly->name)

@php
    $editing = $record !== null;
    $action = $editing
        ? route('medical-records.update', $record)
        : route('medical-records.store');

    // Baris diagnosis & tindakan diisi ulang dari model saat ubah, atau dari
    // input sebelumnya saat validasi gagal.
    $diagnosisRows = old(
        'diagnoses',
        $editing
            ? $record->diagnoses->map(fn ($d) => [
                'icd10_code_id' => $d->icd10_code_id,
                'diagnosis_type' => $d->diagnosis_type->value,
                'description' => $d->description,
            ])->all()
            : []
    );

    $procedureRows = old(
        'procedures',
        $editing
            ? $record->procedures->map(fn ($p) => [
                'name' => $p->name,
                'code' => $p->code,
                'fee' => $p->fee,
                'notes' => $p->notes,
            ])->all()
            : []
    );

    // Minimal satu baris diagnosis tersedia agar pengguna tidak perlu
    // mencari tombol tambah terlebih dahulu.
    if ($diagnosisRows === []) {
        $diagnosisRows = [['icd10_code_id' => '', 'diagnosis_type' => 'PRIMARY', 'description' => '']];
    }
@endphp

@section('content')
    <form method="POST" action="{{ $action }}"
          class="grid grid-cols-1 gap-5 lg:grid-cols-3"
          x-data="{
              diagnoses: @js($diagnosisRows),
              procedures: @js($procedureRows),
              addDiagnosis() { this.diagnoses.push({ icd10_code_id: '', diagnosis_type: 'SECONDARY', description: '' }) },
              removeDiagnosis(index) { this.diagnoses.splice(index, 1); if (this.diagnoses.length === 0) this.addDiagnosis() },
              addProcedure() { this.procedures.push({ name: '', code: '', fee: 0, notes: '' }) },
              removeProcedure(index) { this.procedures.splice(index, 1) },
          }">

        @csrf
        @if ($editing)
            @method('PUT')
        @endif
        <input type="hidden" name="registration_id" value="{{ $registration->id }}">

        <div class="space-y-5 lg:col-span-2">

            {{-- ---------- Kunjungan ---------- --}}
            <x-card title="Kunjungan" subtitle="Data kunjunganasal mula pemeriksaan">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs text-ink-3">Pasien</dt>
                        <dd class="mt-0.5 font-semibold text-ink">{{ $registration->patient->name }}</dd>
                        <dd class="font-mono text-xs text-ink-3">
                            {{ $registration->patient->mr_number }} &middot;
                            {{ $registration->patient->gender->label() }} &middot;
                            {{ $registration->patient->age }} th
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs text-ink-3">Kunjungan</dt>
                        <dd class="mt-0.5 font-mono font-semibold text-ink">{{ $registration->queue_code }}</dd>
                        <dd class="text-xs text-ink-3">
                            {{ $registration->poly->name }} &middot;
                            {{ $registration->visit_date->translatedFormat('d F Y') }}
                        </dd>
                    </div>

                    <div class="sm:col-span-2">
                        <x-select name="doctor_id" label="Dokter Pemeriksa" required
                                  :value="old('doctor_id', $record?->doctor_id)"
                                  :options="$doctors->mapWithKeys(fn ($d) => [$d->id => $d->name])->all()"
                                  placeholder="Pilih dokter" />
                    </div>

                    @if ($registration->complaint)
                        <div class="sm:col-span-2">
                            <dt class="text-xs text-ink-3">Keluhan Utama (dari pendaftaran)</dt>
                            <dd class="mt-1 rounded-lg bg-surface-2 px-3 py-2 text-sm text-ink">
                                {{ $registration->complaint }}
                            </dd>
                        </div>
                    @endif
                </div>
            </x-card>

            {{-- ---------- Tanda vital ---------- --}}
            <x-card title="Tanda Vital" subtitle="Diisi sesuai hasil pengukuran">
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                    <x-input name="blood_pressure" label="Tekanan Darah" :maxlength="20"
                             :value="old('blood_pressure', $record?->blood_pressure)"
                             placeholder="120/80" suffix="mmHg" />

                    <x-input name="pulse" label="Nadi" type="number" min="0" max="300"
                             :value="old('pulse', $record?->pulse)" suffix="/mnt" />

                    <x-input name="temperature" label="Suhu Tubuh" type="number" step="0.1" min="30" max="45"
                             :value="old('temperature', $record?->temperature)" suffix="°C" />

                    <x-input name="respiratory_rate" label="Frekuensi Napas" type="number" min="0" max="100"
                             :value="old('respiratory_rate', $record?->respiratory_rate)" suffix="/mnt" />

                    <x-input name="weight" label="Berat Badan" type="number" step="0.1" min="0.5" max="500"
                             :value="old('weight', $record?->weight)" suffix="kg" />

                    <x-input name="height" label="Tinggi Badan" type="number" step="0.1" min="20" max="260"
                             :value="old('height', $record?->height)" suffix="cm" />
                </div>
            </x-card>

            {{-- ---------- Anamnesis & pemeriksaan ---------- --}}
            <x-card title="Anamnesis dan Pemeriksaan">
                <div class="space-y-4">
                    <x-input name="chief_complaint" label="Keluhan Utama" :maxlength="255"
                             :value="old('chief_complaint', $record?->chief_complaint)"
                             placeholder="Contoh: Demam dan sakit kepala sejak tiga hari lalu" />

                    <x-textarea name="anamnesis" label="Anamnesis" :rows="4" :maxlength="5000"
                                :value="old('anamnesis', $record?->anamnesis)"
                                placeholder="Riwayat penyakit sekarang, sebelumnya, riwayat keluarga, dan lainnya." />

                    <x-textarea name="physical_exam" label="Pemeriksaan Fisik" :rows="4" :maxlength="5000"
                                :value="old('physical_exam', $record?->physical_exam)"
                                placeholder="Hasil pemeriksaan fisik yang dilakukan." />

                    <x-textarea name="diagnosis" label="Ringkasan Diagnosis" :rows="3" :maxlength="5000"
                                :value="old('diagnosis', $record?->diagnosis)"
                                placeholder="Kesimpulan diagnosis dalam kalimat yang mudah dipahami."
                                hint="Wajib diisi sebelum rekam medis dapat difinalisasi." />

                    <x-textarea name="treatment_plan" label="Rencana Terapi" :rows="3" :maxlength="5000"
                                :value="old('treatment_plan', $record?->treatment_plan)"
                                placeholder="Terapi, obat, dan anjuran yang diberikan." />

                    <x-textarea name="notes" label="Catatan Tambahan" :rows="2" :maxlength="5000"
                                :value="old('notes', $record?->notes)"
                                placeholder="Catatan internal dokter." />
                </div>
            </x-card>

            {{-- ---------- Diagnosis ICD-10 ---------- --}}
            <x-card title="Diagnosis Kode ICD-10" subtitle="Minimal satu kode diagnosis wajib diisi">
                <div class="space-y-3">
                    <template x-for="(row, index) in diagnoses" :key="index">
                        <div class="rounded-lg border border-line p-3">
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-12">
                                <div class="sm:col-span-4">
                                    <label :for="'diagnosis_type_' + index" class="field-label">Jenis</label>
                                    <select :id="'diagnosis_type_' + index"
                                            :name="'diagnoses[' + index + '][diagnosis_type]'"
                                            x-model="row.diagnosis_type" class="field-control">
                                        @foreach ($diagnosisTypes as $type)
                                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="sm:col-span-6">
                                    <label :for="'icd10_' + index" class="field-label">Kode ICD-10</label>
                                    <select :id="'icd10_' + index"
                                            :name="'diagnoses[' + index + '][icd10_code_id]'"
                                            x-model="row.icd10_code_id" class="field-control">
                                        <option value="">Pilih kode</option>
                                        @foreach ($icd10Codes as $code)
                                            <option value="{{ $code->id }}">{{ $code->code }} &mdash; {{ $code->description }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="flex items-end sm:col-span-2">
                                    <button type="button" x-on:click="removeDiagnosis(index)"
                                            class="btn btn-sm btn-danger w-full"
                                            :aria-label="'Hapus diagnosis baris ' + (index + 1)">
                                        Hapus
                                    </button>
                                </div>
                            </div>

                            <div class="mt-3">
                                <label :for="'desc_' + index" class="field-label">Deskripsi Diagnosis</label>
                                <input :id="'desc_' + index"
                                       type="text"
                                       :name="'diagnoses[' + index + '][description]'"
                                       x-model="row.description"
                                       maxlength="255"
                                       class="field-control"
                                       placeholder="Diisi otomatis dari kode ICD-10 bila dikosongkan">
                            </div>
                        </div>
                    </template>

                    <button type="button" x-on:click="addDiagnosis()" class="btn btn-sm btn-secondary">
                        Tambah Diagnosis
                    </button>
                </div>
            </x-card>

            {{-- ---------- Tindakan ---------- --}}
            <x-card title="Tindakan / Prosedur" subtitle="Biaya tindakan akan masuk ke tagihan otomatis">
                <div class="space-y-3">
                    <template x-for="(row, index) in procedures" :key="index">
                        <div class="grid grid-cols-1 gap-3 rounded-lg border border-line p-3 sm:grid-cols-12">
                            <div class="sm:col-span-5">
                                <label :for="'proc_name_' + index" class="field-label">Nama Tindakan</label>
                                <input :id="'proc_name_' + index" type="text"
                                       :name="'procedures[' + index + '][name]'"
                                       x-model="row.name" maxlength="150" class="field-control"
                                       placeholder="Contoh: Jahit Luka">
                            </div>

                            <div class="sm:col-span-2">
                                <label :for="'proc_code_' + index" class="field-label">Kode</label>
                                <input :id="'proc_code_' + index" type="text"
                                       :name="'procedures[' + index + '][code]'"
                                       x-model="row.code" maxlength="30" class="field-control" placeholder="Opsional">
                            </div>

                            <div class="sm:col-span-3">
                                <label :for="'proc_fee_' + index" class="field-label">Biaya</label>
                                <input :id="'proc_fee_' + index" type="number" min="0" max="100000000"
                                       :name="'procedures[' + index + '][fee]'"
                                       x-model.number="row.fee" class="field-control" placeholder="0">
                            </div>

                            <div class="flex items-end sm:col-span-2">
                                <button type="button" x-on:click="removeProcedure(index)"
                                        class="btn btn-sm btn-danger w-full"
                                        :aria-label="'Hapus tindakan baris ' + (index + 1)">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </template>

                    <button type="button" x-on:click="addProcedure()" class="btn btn-sm btn-secondary">
                        Tambah Tindakan
                    </button>
                </div>
            </x-card>

            {{-- ---------- Aksi ---------- --}}
            <div class="flex flex-wrap items-center justify-end gap-2">
                <x-button :href="route('medical-records.index')" variant="ghost">Batal</x-button>
                <x-button type="submit" icon="check">
                    {{ $editing ? 'Simpan Perubahan' : 'Simpan Rekam Medis' }}
                </x-button>
            </div>
        </div>

        {{-- ---------- Panel samping ---------- --}}
        <div class="space-y-4">
            <x-card title="Tentang Rekam Medis">
                <ul class="space-y-2.5 text-xs leading-relaxed text-ink-2">
                    <li class="flex gap-2">
                        <x-icon name="info" class="mt-px h-3.5 w-3.5 shrink-0 text-ink-3" />
                        <span>Rekam medis disimpan sebagai <strong>draft</strong> dan masih dapat diubah.</span>
                    </li>
                    <li class="flex gap-2">
                        <x-icon name="lock" class="mt-px h-3.5 w-3.5 shrink-0 text-ink-3" />
                        <span>Setelah difinalisasi, rekam medis <strong>tidak dapat diubah atau dihapus</strong> demi kepatuhan.</span>
                    </li>
                    <li class="flex gap-2">
                        <x-icon name="check" class="mt-px h-3.5 w-3.5 shrink-0 text-ink-3" />
                        <span>Kode diagnosis (ICD-10) dan ringkasan diagnosis wajib diisi untuk finalisasi.</span>
                    </li>
                    <li class="flex gap-2">
                        <x-icon name="receipt" class="mt-px h-3.5 w-3.5 shrink-0 text-ink-3" />
                        <span>Biaya tindakan otomatis masuk ke tagihan setelah rekam medis difinalisasi.</span>
                    </li>
                </ul>
            </x-card>

            <x-card title="Navigasi">
                <div class="space-y-2">
                    <a href="{{ route('registrations.show', $registration) }}" class="nav-link w-full">
                        <x-icon name="clipboard" class="h-4 w-4" />
                        Detail Kunjungan
                    </a>
                    <a href="{{ route('patients.show', $registration->patient) }}" class="nav-link w-full">
                        <x-icon name="users" class="h-4 w-4" />
                        Profil Pasien
                    </a>
                </div>
            </x-card>
        </div>
    </form>
@endsection
