<form method="POST" action="{{ route('demo.store') }}" class="space-y-4 px-6 py-6">
    @csrf

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="demo-name" class="mb-1.5 block text-sm font-medium text-slate-700">Nama lengkap <span class="text-rose-500">*</span></label>
            <input id="demo-name" name="name" type="text" required autocomplete="name" value="{{ old('name') }}"
                   class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset {{ $errors->has('name') ? 'ring-rose-400' : 'ring-slate-300 focus:ring-blue-600' }}">
            @error('name')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="demo-position" class="mb-1.5 block text-sm font-medium text-slate-700">Jabatan</label>
            <input id="demo-position" name="position" type="text" placeholder="Contoh: Direktur / Kepala IT" value="{{ old('position') }}"
                   class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset {{ $errors->has('position') ? 'ring-rose-400' : 'ring-slate-300 focus:ring-blue-600' }}">
            @error('position')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div>
        <label for="demo-institution" class="mb-1.5 block text-sm font-medium text-slate-700">Nama rumah sakit / klinik <span class="text-rose-500">*</span></label>
        <input id="demo-institution" name="institution" type="text" required value="{{ old('institution') }}"
               placeholder="Contoh: RS Umum Harapan Bangsa"
               class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset {{ $errors->has('institution') ? 'ring-rose-400' : 'ring-slate-300 focus:ring-blue-600' }}">
        @error('institution')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="demo-email" class="mb-1.5 block text-sm font-medium text-slate-700">Email kantor <span class="text-rose-500">*</span></label>
            <input id="demo-email" name="email" type="email" required autocomplete="email" value="{{ old('email') }}"
                   class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset {{ $errors->has('email') ? 'ring-rose-400' : 'ring-slate-300 focus:ring-blue-600' }}">
            @error('email')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="demo-phone" class="mb-1.5 block text-sm font-medium text-slate-700">Nomor WhatsApp / telepon <span class="text-rose-500">*</span></label>
            <input id="demo-phone" name="phone" type="tel" required autocomplete="tel" value="{{ old('phone') }}"
                   placeholder="0812xxxxxxx"
                   class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset {{ $errors->has('phone') ? 'ring-rose-400' : 'ring-slate-300 focus:ring-blue-600' }}">
            @error('phone')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="demo-facility" class="mb-1.5 block text-sm font-medium text-slate-700">Jenis fasilitas <span class="text-rose-500">*</span></label>
            <select id="demo-facility" name="facility_type" required
                    class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset {{ $errors->has('facility_type') ? 'ring-rose-400' : 'ring-slate-300 focus:ring-blue-600' }}">
                <option value="HOSPITAL" @selected(old('facility_type', 'HOSPITAL'))>Rumah Sakit</option>
                <option value="CLINIC" @selected(old('facility_type') === 'CLINIC')>Klinik</option>
                <option value="PRIVATE_PRACTICE" @selected(old('facility_type') === 'PRIVATE_PRACTICE')>Praktik Mandiri</option>
                <option value="OTHER" @selected(old('facility_type') === 'OTHER')>Lainnya</option>
            </select>
            @error('facility_type')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="demo-bed" class="mb-1.5 block text-sm font-medium text-slate-700">Jumlah tempat tidur</label>
            <input id="demo-bed" name="bed_count" type="number" min="0" max="5000" value="{{ old('bed_count') }}"
                   placeholder="100"
                   class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset {{ $errors->has('bed_count') ? 'ring-rose-400' : 'ring-slate-300 focus:ring-blue-600' }}">
            @error('bed_count')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div>
        <label for="demo-message" class="mb-1.5 block text-sm font-medium text-slate-700">Kebutuhan Anda <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
        <textarea id="demo-message" name="message" rows="3"
                  placeholder="Contoh: Kami masih memakai pencatatan manual, sulit untuk klaim BPJS, dan butuh integrasi dengan apotek."
                  class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset {{ $errors->has('message') ? 'ring-rose-400' : 'ring-slate-300 focus:ring-blue-600' }}">{{ old('message') }}</textarea>
        @error('message')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="hidden" aria-hidden="true">
        <label for="company_website">Website perusahaan</label>
        <input id="company_website" name="company_website" type="text" tabindex="-1" autocomplete="off">
    </div>

    <button type="submit"
            class="w-full rounded-lg bg-blue-700 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
        Jadwalkan Demo Gratis
    </button>

    <p class="text-center text-xs text-slate-500">
        Dengan mengirim formulir ini, Anda menyetujui
        <span class="font-medium text-slate-600">Kebijakan Privasi</span> kami.
    </p>
</form>
