@php
    /**
     * Formulir bersama untuk tambah dan ubah data pasien.
     *
     * @var bool $editing
     * @var \App\Models\Patient $patient
     */
    $editing = $editing ?? false;
    $action = $editing ? route('patients.update', $patient) : route('patients.store');
    $currentInsurance = old('insurance_type', $patient->insurance_type?->value ?? \App\Enums\InsuranceType::NONE->value);
@endphp

<form method="POST" action="{{ $action }}" class="grid grid-cols-1 gap-5 lg:grid-cols-3"
      x-data="{ insurance: @js($currentInsurance) }">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <div class="space-y-5 lg:col-span-2">

        {{-- ---------- Identitas ---------- --}}
        <x-card title="Identitas Pasien" subtitle="Data dasar sesuai dokumen resmi">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="field-label">
                        Nama Lengkap <span class="text-danger-ink" aria-hidden="true">*</span>
                        <span class="sr-only">(wajib diisi)</span>
                    </label>
                    <input id="name" name="name" type="text" required maxlength="150"
                           value="{{ old('name', $patient->name) }}"
                           placeholder="Nama sesuai KTP/SIM"
                           class="field-control @error('name') field-control-error @enderror"
                           @error('name') aria-invalid="true" @enderror>
                    @error('name')
                        <p class="field-error"><x-icon name="exclamation" class="h-3.5 w-3.5 shrink-0" /><span>{{ $message }}</span></p>
                    @enderror
                </div>

                <div>
                    <x-input name="nik" label="NIK" required maxlength="16" inputmode="numeric"
                             :value="old('nik', $patient->exists ? $patient->nik : null)"
                             placeholder="16 digit angka" autocomplete="off"
                             x-on:input="$el.value = $el.value.replace(/\D/g, '').slice(0, 16)"
                             hint="Nomor Induk Kependudukan sesuai KTP." />

                    @if ($patient->exists)
                        <p class="mt-1.5 text-xs text-ink-3">
                            NIK tersimpan terenkripsi. Demi kerahasiaan, <span class="font-mono">{{ $patient->masked_nik }}</span> ditampilkan.
                        </p>
                    @endif
                </div>

                <div>
                    <x-select name="gender" label="Jenis Kelamin" required
                              :value="old('gender', $patient->gender?->value)"
                              :options="collect($genders)->map(fn ($g) => ['value' => $g->value, 'label' => $g->label()])->all()"
                              placeholder="Pilih jenis kelamin" />
                </div>

                <x-input name="birth_date" label="Tanggal Lahir" type="date" required
                         :value="old('birth_date', $patient->birth_date?->toDateString())"
                         max="{{ now()->toDateString() }}" min="1900-01-01" />

                <x-input name="birth_place" label="Tempat Lahir" :maxlength="100"
                         :value="old('birth_place', $patient->birth_place)"
                         placeholder="Contoh: Bandung" />

                <x-select name="blood_type" label="Golongan Darah"
                          :value="old('blood_type', $patient->blood_type)"
                          :options="['A', 'B', 'AB', 'O', '-']" placeholder="Tidak diisi" />

                <x-select name="marital_status" label="Status Kawin"
                          :value="old('marital_status', $patient->marital_status)"
                          :options="['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati']"
                          placeholder="Tidak diisi" />
            </div>
        </x-card>

        {{-- ---------- Kontak & Alamat ---------- --}}
        <x-card title="Kontak dan Alamat">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-input name="phone" label="Nomor Telepon" :maxlength="25"
                         :value="old('phone', $patient->phone)" placeholder="08xxxxxxxxxx"
                         inputmode="tel" autocomplete="tel" />

                <x-textarea name="address" label="Alamat (Jalan)" :rows="2" :maxlength="1000"
                            :value="old('address', $patient->address)"
                            placeholder="Nama jalan, nomor rumah, RT/RW" class="sm:col-span-2" />

                <x-input name="village" label="Desa / Kelurahan" :maxlength="100"
                         :value="old('village', $patient->village)" />

                <x-input name="district" label="Kecamatan" :maxlength="100"
                         :value="old('district', $patient->district)" />

                <x-input name="city" label="Kota / Kabupaten" :maxlength="100"
                         :value="old('city', $patient->city)" />

                <x-input name="province" label="Provinsi" :maxlength="100"
                         :value="old('province', $patient->province)" />

                <x-input name="postal_code" label="Kode Pos" :maxlength="10"
                         :value="old('postal_code', $patient->postal_code)" inputmode="numeric" />
            </div>
        </x-card>

        {{-- ---------- Asuransi & Darurat ---------- --}}
        <x-card title="Asuransi dan Kontak Darurat">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="insurance_type" class="field-label">
                        Jenis Aseteksi <span class="text-danger-ink" aria-hidden="true">*</span>
                        <span class="sr-only">(wajib diisi)</span>
                    </label>
                    <select id="insurance_type" name="insurance_type" required x-model="insurance"
                            @error('insurance_type') aria-invalid="true" @enderror
                            class="field-control @error('insurance_type') field-control-error @enderror">
                        @foreach ($insuranceTypes as $type)
                            <option value="{{ $type->value }}" @selected($currentInsurance === $type->value)>
                                {{ $type->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('insurance_type')
                        <p class="field-error"><x-icon name="exclamation" class="h-3.5 w-3.5 shrink-0" /><span>{{ $message }}</span></p>
                    @enderror
                </div>

                <div x-show="insurance !== 'NONE'" x-cloak>
                    <x-input name="insurance_number" label="Nomor BPJS / Asuransi" :maxlength="100"
                             :value="old('insurance_number', $patient->insurance_number)"
                             placeholder="Nomor kartu BPJS atau polis"
                             x-bind:required="insurance !== 'NONE'" />
                </div>

                <div x-show="insurance !== 'NONE'" x-cloak>
                    <x-input name="insurance_name" label="Nama Penjamin Asuransi" :maxlength="150"
                             :value="old('insurance_name', $patient->insurance_name)"
                             placeholder="Nama kantor asuransi atau perusahaan" />
                </div>

                <x-input name="emergency_contact_name" label="Nama Kontak Darurat" :maxlength="150"
                         :value="old('emergency_contact_name', $patient->emergency_contact_name)"
                         placeholder="Nama kerabat / wali" />

                <x-input name="emergency_contact_phone" label="Telepon Kontak Darurat" :maxlength="25"
                         :value="old('emergency_contact_phone', $patient->emergency_contact_phone)"
                         placeholder="08xxxxxxxxxx" inputmode="tel" />
            </div>
        </x-card>

        {{-- ---------- Aksi ---------- --}}
        <div class="flex flex-wrap items-center justify-end gap-2">
            <x-button :href="route('patients.index')" variant="ghost">Batal</x-button>
            <x-button type="submit" icon="check">
                {{ $editing ? 'Simpan Perubahan' : 'Daftarkan Pasien' }}
            </x-button>
        </div>
    </div>

    {{-- ---------- Ringkasan ---------- --}}
    <div class="space-y-4">
        <x-card title="Ringkasan" padding="p-0">
            <dl class="divide-y divide-line text-sm">
                <div class="flex items-center justify-between gap-3 px-5 py-3">
                    <dt class="text-ink-3">Nomor Rekam Medis</dt>
                    <dd class="font-mono font-bold text-ink">
                        {{ $patient->exists ? $patient->mr_number : 'Otomatis' }}
                    </dd>
                </div>
                <div class="flex items-center justify-between gap-3 px-5 py-3">
                    <dt class="text-ink-3">Tanggal Daftar</dt>
                    <dd class="font-medium text-ink">
                        {{ $patient->created_at?->translatedFormat('d M Y') ?? now()->translatedFormat('d M Y') }}
                    </dd>
                </div>
            </dl>
        </x-card>

        <x-card title="Petunjuk Pengisian">
            <ul class="space-y-2.5 text-xs leading-relaxed text-ink-2">
                <li class="flex gap-2">
                    <x-icon name="check" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-brand" />
                    <span>NIK wajib 16 digit dan <strong>unik</strong>. Data dengan NIK sama tidak dapat didaftarkan ulang.</span>
                </li>
                <li class="flex gap-2">
                    <x-icon name="check" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-brand" />
                    <span>NIK disimpan terenkripsi di database (AES-256) dan ditampilkan dalam bentuk tersamar.</span>
                </li>
                <li class="flex gap-2">
                    <x-icon name="check" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-brand" />
                    <span>Nomor rekam medis dibuat otomatis oleh sistem dan tidak dapat diubah.</span>
                </li>
                <li class="flex gap-2">
                    <x-icon name="check" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-brand" />
                    <span>Pilih "Mandiri" bila pasien tidak menggunakan penjamin biaya.</span>
                </li>
            </ul>
        </x-card>
    </div>
</form>
