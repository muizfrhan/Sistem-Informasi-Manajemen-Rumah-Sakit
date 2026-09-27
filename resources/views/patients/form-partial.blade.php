@php
    $isEdit = $patient->exists;
    $bpjsRequired = old('insurance_type', $patient->insurance_type?->value) !== 'NONE';
@endphp

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <form method="POST"
          action="{{ $isEdit ? route('patients.update', $patient) : route('patients.store') }}"
          class="space-y-6 lg:col-span-2">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        <x-card title="Identitas Diri" subtitle="Data sesuai dokumen resmi pasien">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-input name="name" label="Nama Lengkap" :value="$patient->name" required
                         placeholder="Sesuai KTP" class="sm:col-span-2" autocomplete="name" />

                <x-input name="nik" label="NIK" :value="$patient->nik" required inputmode="numeric"
                         maxlength="16" hint="16 digit sesuai KTP, dienkripsi pada database"
                         x-data="{}" oninput="this.value=this.value.replace(/\D/g,'')" />

                <x-select name="gender" label="Jenis Kelamin" :value="$patient->gender" required
                          :options="collect($genders)->mapWithKeys(fn ($g) => [$g->value => $g->label()])->all()"
                          placeholder="Pilih jenis kelamin" />

                <x-input name="birth_place" label="Tempat Lahir" :value="$patient->birth_place" placeholder="Kota/Kabupaten" />

                <x-input name="birth_date" label="Tanggal Lahir" type="date" :value="optional($patient->birth_date)->format('Y-m-d')" required :max="now()->format('Y-m-d')" />

                <x-select name="blood_type" label="Golongan Darah" :value="$patient->blood_type"
                          :options="['A' => 'A', 'B' => 'B', 'AB' => 'AB', 'O' => 'O', '-' => 'Tidak Tahu']"
                          placeholder="Pilih golongan darah" />

                <x-select name="marital_status" label="Status Perkawinan" :value="$patient->marital_status"
                          :options="['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati']" placeholder="Pilih status" />
            </div>
        </x-card>

        <x-card title="Kontak & Alamat">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-input name="phone" label="Nomor Telepon" :value="$patient->phone" placeholder="08xxxxxxxxxx" autocomplete="tel" />

                <x-input name="postal_code" label="Kode Pos" :value="$patient->postal_code" maxlength="5" inputmode="numeric" />

                <x-textarea name="address" label="Alamat Lengkap" :value="$patient->address" :rows="2"
                            placeholder="Nama jalan, nomor rumah, RT/RW" class="sm:col-span-2" />

                <x-input name="village" label="Kelurahan / Desa" :value="$patient->village" />
                <x-input name="district" label="Kecamatan" :value="$patient->district" />
                <x-input name="city" label="Kota / Kabupaten" :value="$patient->city" />
                <x-input name="province" label="Provinsi" :value="$patient->province" />
            </div>
        </x-card>

        <x-card title="Jaminan Kesehatan">
            <div x-data="{ type: @js(old('insurance_type', $patient->insurance_type?->value ?? 'NONE')) }" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="insurance_type" class="mb-1.5 block text-sm font-medium text-slate-700">Jenis Jaminan <span class="text-rose-500">*</span></label>
                        <select id="insurance_type" name="insurance_type" required x-model="type"
                                class="block w-full rounded-lg border-0 px-3 py-2 text-slate-900 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm {{ $errors->has('insurance_type') ? 'ring-rose-400' : 'ring-slate-300 focus:ring-teal-600' }}">
                            @foreach ($insuranceTypes as $type)
                                <option value="{{ $type->value }}">{{ $type->label() }}</option>
                            @endforeach
                        </select>
                        @error('insurance_type')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div x-show="type !== 'NONE'" x-cloak>
                        <x-input name="insurance_name" label="Nama Penetap Asuransi" :value="$patient->insurance_name" placeholder="BPJS Kesehatan / PT Asuransi" />
                    </div>
                </div>

                <div x-show="type !== 'NONE'" x-cloak>
                    <x-input name="insurance_number" label="Nomor Peserta" :value="$patient->insurance_number"
                             placeholder="Nomor kartu BPJS / polis" />
                </div>
            </div>
        </x-card>

        <x-card title="Kontak Darurat">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-input name="emergency_contact_name" label="Nama Lengkap" :value="$patient->emergency_contact_name" />
                <x-input name="emergency_contact_phone" label="Nomor Telepon" :value="$patient->emergency_contact_phone" />
            </div>
        </x-card>

        <div class="flex flex-wrap items-center justify-end gap-3">
            <x-button :href="route('patients.index')" variant="secondary">Batal</x-button>
            <x-button type="submit">{{ $isEdit ? 'Simpan Perubahan' : 'Daftarkan Pasien' }}</x-button>
        </div>
    </form>

    <div class="space-y-6">
        <x-card title="Ringkasan">
            <dl class="space-y-2.5 text-sm">
                <div class="flex justify-between gap-3"><dt class="text-slate-500">No. Rekam Medis</dt><dd class="font-mono font-semibold text-slate-800">{{ $patient->mr_number ?? 'Otomatis' }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-500">Jaminan</dt><dd class="text-slate-800">{{ old('insurance_type', $patient->insurance_type?->value ?? 'NONE') === 'NONE' ? 'Mandiri' : \App\Enums\InsuranceType::from(old('insurance_type', $patient->insurance_type?->value))->label() }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-500">Tanggal Daftar</dt><dd class="text-slate-800">{{ now()->translatedFormat('d M Y') }}</dd></div>
            </dl>
        </x-card>

        <x-card title="Catatan Validasi">
            <ul class="space-y-2 text-xs text-slate-600">
                <li class="flex gap-2"><span class="text-teal-600">&#10003;</span> NIK wajib 16 digit dan unik antar pasien.</li>
                <li class="flex gap-2"><span class="text-teal-600">&#10003;</span> NIK dienkripsi (AES-256) dan tidak dapat dibaca langsung dari database.</li>
                <li class="flex gap-2"><span class="text-teal-600">&#10003;</span> Nomor rekam medis dibuat otomatis dan berurutan.</li>
                <li class="flex gap-2"><span class="text-teal-600">&#10003;</span> Nomor peserta wajib diisi jika memilih jaminan selain Mandiri.</li>
            </ul>
        </x-card>
    </div>
</div>
