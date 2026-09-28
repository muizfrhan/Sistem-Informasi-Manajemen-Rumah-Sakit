@extends('layouts.app')

@section('title', 'Permintaan Demo')
@section('heading', 'Permintaan Demo')
@section('subheading', $leads->total().' calon klien dari halaman landing')

@php
    $hasFilter = $search !== '' || $status !== '' || $facilityType !== '';
    $resetUrl = route('admin.demo-requests.index');

    $statusOptions = [
        'NEW' => 'Baru',
        'CONTACTED' => 'Sudah Dihubungi',
        'DEMO_SCHEDULED' => 'Demo Dijadwalkan',
        'WON' => 'Menjadi Klien',
        'LOST' => 'Belum Tertarik',
    ];

    $statusVariants = [
        'NEW' => 'brand',
        'CONTACTED' => 'info',
        'DEMO_SCHEDULED' => 'warning',
        'WON' => 'success',
        'LOST' => 'slate',
    ];

    $pageDescription = 'Total '.number_format($leads->total(), 0, ',', '.').' permintaan'
        .($search !== '' ? ' untuk pencarian '.$search : '');

    $emptyCta = $hasFilter
        ? ['label' => 'Reset Filter', 'href' => $resetUrl, 'variant' => 'secondary', 'icon' => 'refresh']
        : ['label' => 'Belum ada permintaan', 'href' => null, 'variant' => 'secondary', 'icon' => 'inbox'];
@endphp

@section('content')
    <div class="space-y-5">
        <x-page-header title="Permintaan Demo" :description="$pageDescription" />

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
            <x-stat-card label="Total Permintaan" :value="number_format($leads->total(), 0, ',', '.')"
                         unit="permintaan" tone="slate" icon="inbox" compact />
            <x-stat-card label="Baru" :value="number_format($counts->get('NEW', 0), 0, ',', '.')"
                         unit="permintaan" tone="brand" icon="bell" compact />
            <x-stat-card label="Sudah Dihubungi" :value="number_format($counts->get('CONTACTED', 0), 0, ',', '.')"
                         unit="permintaan" tone="info" icon="phone" compact />
            <x-stat-card label="Demo Dijadwalkan" :value="number_format($counts->get('DEMO_SCHEDULED', 0), 0, ',', '.')"
                         unit="permintaan" tone="warning" icon="calendar" compact />
            <x-stat-card label="Menjadi Klien" :value="number_format($counts->get('WON', 0), 0, ',', '.')"
                         unit="permintaan" tone="success" icon="check-circle" compact />
        </div>

        <x-card padding="p-0">
            <x-slot:title>Daftar Permintaan</x-slot:title>
            <x-slot:subtitle>Data diisi melalui formulir jadwalkan demo pada halaman landing</x-slot:subtitle>

            <x-filter-bar :action="$resetUrl" :reset="$resetUrl">
                <x-slot:fields>
                    <div>
                        <label for="lead_q" class="field-label">Pencarian</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-ink-3">
                                <x-icon name="search" class="h-4 w-4" />
                            </span>
                            <input id="lead_q" name="q" type="search" value="{{ $search }}"
                                   placeholder="Nama, instansi, atau email" class="field-control pl-9">
                        </div>
                    </div>

                    <div>
                        <label for="lead_status" class="field-label">Status</label>
                        <select id="lead_status" name="status" class="field-control">
                            <option value="">Semua status</option>
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="lead_facility_type" class="field-label">Jenis Fasilitas</label>
                        <select id="lead_facility_type" name="facility_type" class="field-control">
                            <option value="">Semua jenis</option>
                            <option value="HOSPITAL" @selected($facilityType === 'HOSPITAL')>Rumah Sakit</option>
                            <option value="CLINIC" @selected($facilityType === 'CLINIC')>Klinik</option>
                            <option value="PRIVATE_PRACTICE" @selected($facilityType === 'PRIVATE_PRACTICE')>Praktik Mandiri</option>
                            <option value="OTHER" @selected($facilityType === 'OTHER')>Lainnya</option>
                        </select>
                    </div>
                </x-slot:fields>

                <x-slot:default>
                    <span class="text-xs text-ink-3">
                        Menampilkan {{ $leads->firstItem() ?? 0 }}&ndash;{{ $leads->lastItem() ?? 0 }}
                        dari {{ number_format($leads->total(), 0, ',', '.') }} permintaan
                    </span>
                </x-slot:default>
            </x-filter-bar>

            <x-data-table :cols="$leads->count()" min-width="68rem"
                          empty="Belum ada permintaan demo"
                          empty-message="Permintaan akan muncul di sini setelah calon klien mengisi formulir pada halaman landing."
                          empty-icon="inbox">
                <x-slot:emptyAction>
                    <x-button type="button" :href="$emptyCta['href']" :variant="$emptyCta['variant']" size="sm"
                              :icon="$emptyCta['icon']" x-on:click="location.href = '{{ $resetUrl }}'">
                        {{ $emptyCta['label'] }}
                    </x-button>
                </x-slot:emptyAction>

                <x-slot:head>
                    <tr>
                        <th scope="col" class="hidden sm:table-cell">Tanggal</th>
                        <th scope="col">Instansi</th>
                        <th scope="col">Calon Klien</th>
                        <th scope="col" class="hidden lg:table-cell">Kontak</th>
                        <th scope="col" class="hidden xl:table-cell">Kebutuhan</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-right">Aksi</th>
                    </tr>
                </x-slot:head>

                <x-slot:body>
                    @foreach ($leads as $lead)
                        @php
                            $email = $lead->email ?: null;
                            $phoneDigits = preg_replace('/\D/', '', (string) $lead->phone);
                            $phoneDigits = $phoneDigits !== '' ? $phoneDigits : null;
                            $note = (string) ($lead->internal_note ?? '');
                        @endphp

                        <tr>
                            <td data-label="Tanggal" class="hidden sm:table-cell">
                                <span class="text-xs text-ink-2 tabular-nums">
                                    {{ $lead->created_at?->translatedFormat('d M Y H:i') }}
                                </span>
                            </td>

                            <td data-label="Instansi">
                                <span class="font-semibold text-ink">{{ $lead->institution }}</span>
                                <span class="mt-0.5 block text-xs text-ink-3">
                                    {{ $lead->facilityTypeLabel() }}@if ($lead->bed_count) &middot; {{ $lead->bed_count }} TT @endif
                                </span>
                                <span class="mt-0.5 block text-xs text-ink-3 sm:hidden">
                                    {{ $lead->created_at?->translatedFormat('d M Y') }}
                                </span>
                            </td>

                            <td data-label="Calon Klien">
                                <span class="text-sm font-medium text-ink">{{ $lead->name }}</span>
                                <span class="mt-0.5 block text-xs text-ink-3">{{ $lead->position ?: 'Tanpa jabatan' }}</span>
                            </td>

                            <td data-label="Kontak" class="hidden lg:table-cell">
                                @if ($email)
                                    <a href="mailto:{{ $email }}" class="text-xs font-medium text-brand hover:underline">
                                        {{ $email }}
                                    </a>
                                @else
                                    <span class="text-xs text-ink-3">Tanpa email</span>
                                @endif

                                @if ($phoneDigits)
                                    <a href="https://wa.me/{{ $phoneDigits }}" target="_blank" rel="noopener noreferrer"
                                       class="mt-0.5 flex items-center gap-1 text-xs text-ink-2 hover:underline">
                                        <x-icon name="phone" class="h-3 w-3" />
                                        {{ $lead->phone }}
                                    </a>
                                @else
                                    <span class="mt-0.5 block text-xs text-ink-3">Tanpa nomor telepon</span>
                                @endif
                            </td>

                            <td data-label="Kebutuhan" class="hidden xl:table-cell">
                                <span class="line-clamp-2 text-xs text-ink-2">
                                    {{ \Illuminate\Support\Str::limit((string) ($lead->message ?? ''), 90) ?: 'Tidak ada keterangan' }}
                                </span>
                            </td>

                            <td data-label="Status">
                                <x-badge :variant="$statusVariants[$lead->status] ?? 'slate'" dot>
                                    {{ $lead->statusLabel() }}
                                </x-badge>
                                @if ($note !== '')
                                    <span class="mt-0.5 block text-[11px] text-ink-3">Ada catatan internal</span>
                                @endif
                            </td>

                            <td data-label="Aksi">
                                <div class="flex flex-wrap items-center justify-end gap-1">
                                    <button type="button" class="btn btn-sm btn-secondary"
                                            x-on:click="$dispatch('manage-lead', @js([
                                                'id' => $lead->id,
                                                'institution' => $lead->institution,
                                                'name' => $lead->name,
                                                'status' => $lead->status,
                                                'internal_note' => $note,
                                                'action' => route('admin.demo-requests.update', $lead),
                                                'delete_action' => route('admin.demo-requests.destroy', $lead),
                                            ]))">
                                        Kelola
                                    </button>

                                    {{-- Formulir hapus berada di luar sel tabel agar
                                         tidak pernah menjadi anak langsung <tbody>. --}}
                                    <form method="POST" action="{{ route('admin.demo-requests.destroy', $lead) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <x-button type="submit" size="sm" variant="danger" icon="trash"
                                                  x-on:click.prevent="$dispatch('confirm', {
                                                      action: $el.form.action,
                                                      method: 'DELETE',
                                                      title: 'Hapus permintaan {{ $lead->institution }}?',
                                                      message: 'Permintaan demo dari {{ $lead->name }} akan dihapus permanen beserta catatan internalnya.',
                                                      confirmLabel: 'Hapus Permintaan',
                                                  })">
                                            Hapus
                                        </x-button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-slot:body>
            </x-data-table>

            @if ($leads->hasPages())
                {{ $leads->links() }}
            @endif
        </x-card>
    </div>

    {{-- ===================== Modal kelola permintaan ===================== --}}
    <div x-data="{
            open: false,
            form: {
                id: null,
                institution: '',
                name: '',
                status: 'NEW',
                internal_note: '',
                action: '',
                delete_action: '',
            },
            openManage(data) {
                this.form = data;
                this.open = true;
            },
         }"
         x-on:manage-lead.window="openManage($event.detail)">

        <x-modal title="Kelola Permintaan Demo" subtitle="Perbarui status tindak lanjut dan catatan internal"
                 icon="clipboard" maxWidth="lg">
            <div class="space-y-4">
                <dl class="grid grid-cols-1 gap-3 rounded-lg bg-surface-2 p-3.5 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-[11px] font-semibold tracking-wide text-ink-3 uppercase">Instansi</dt>
                        <dd class="mt-0.5 font-semibold text-ink" x-text="form.institution"></dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold tracking-wide text-ink-3 uppercase">Calon Klien</dt>
                        <dd class="mt-0.5 font-medium text-ink" x-text="form.name"></dd>
                    </div>
                </dl>

                {{-- Formulir pembaruan dan formulir hapus sengaja dipisah agar
                     tidak ada form yang bersarang. --}}
                <form :action="form.action" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="lead_modal_status" class="field-label">
                            Status Tindak Lanjut <span class="text-danger-ink" aria-hidden="true">*</span>
                        </label>
                        <select id="lead_modal_status" name="status" required x-model="form.status" class="field-control">
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="lead_modal_note" class="field-label">Catatan Internal</label>
                        <textarea id="lead_modal_note" name="internal_note" rows="4" maxlength="2000"
                                  class="field-control resize-y"
                                  placeholder="Riwayat komunikasi, hasil percakapan, atau tindak lanjut berikutnya."
                                  x-model="form.internal_note"></textarea>
                        <p class="field-hint">
                            Hanya terlihat oleh pengelola, maksimal 2.000 karakter.
                        </p>
                    </div>

                    <x-slot:footer>
                        <button type="button" x-on:click="open = false" class="btn btn-md btn-secondary">Batal</button>
                        <button type="submit" class="btn btn-md btn-primary">Simpan Perubahan</button>
                    </x-slot:footer>
                </form>

                <form :action="form.delete_action" method="POST" class="border-t border-line pt-4">
                    @csrf
                    @method('DELETE')
                    <p class="mb-2.5 text-xs text-ink-3">
                        Menghapus permintaan akan menghilangkan catatan internal secara permanen.
                    </p>
                    <x-button type="submit" size="sm" variant="danger" icon="trash"
                              x-on:click.prevent="$dispatch('confirm', {
                                  action: $el.form.action,
                                  method: 'DELETE',
                                  title: 'Hapus permintaan demo ini?',
                                  message: 'Permintaan beserta catatan internalnya akan dihapus permanen.',
                                  confirmLabel: 'Hapus Permintaan',
                              })">
                        Hapus Permintaan
                    </x-button>
                </form>
            </div>
        </x-modal>
    </div>
@endsection
