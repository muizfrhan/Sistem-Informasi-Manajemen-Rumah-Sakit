@extends('layouts.app')

@section('title', 'Data Obat')
@section('heading', 'Data Obat')
@section('subheading', $drugs->total().' obat terdaftar')

@php
    $search = trim((string) request('q', ''));
    $lowStock = request()->boolean('low_stock');
    $category = trim((string) request('category', ''));
    $hasFilter = $search !== '' || $lowStock || $category !== '';

    // Daftar kategori diturunkan dari data yang sedang tampil agar view tidak
    // bergantung pada variabel tambahan dari controller.
    $categories = $drugs->getCollection()
        ->pluck('category')
        ->filter()
        ->unique()
        ->sort()
        ->values();

    $resetUrl = route('master.drugs.index');

    $emptyCta = $hasFilter
        ? ['label' => 'Reset Filter', 'href' => $resetUrl, 'variant' => 'secondary', 'icon' => 'refresh', 'permission' => null, 'click' => "location.href = '".$resetUrl."'"]
        : ['label' => 'Tambah Obat', 'href' => null, 'variant' => 'primary', 'icon' => 'plus', 'permission' => 'master.manage', 'click' => "\$dispatch('open-drug')"];

    $pageDescription = 'Total '.number_format($drugs->total(), 0, ',', '.').' obat'
        .($search !== '' ? ' untuk pencarian '.$search : '');

    // Nilai awal form modal diambil dari old() agar input yang gagal
    // validasi tetap terisi kembali.
    $drugForm = [
        'code' => (string) old('code', ''),
        'name' => (string) old('name', ''),
        'generic_name' => (string) old('generic_name', ''),
        'category' => (string) old('category', ''),
        'form' => (string) old('form', ''),
        'strength' => (string) old('strength', ''),
        'unit' => (string) old('unit', 'Tablet'),
        'price' => (string) old('price', ''),
        'stock' => (string) old('stock', '0'),
        'min_stock' => (string) old('min_stock', '10'),
        'expired_at' => (string) old('expired_at', ''),
        'requires_prescription' => (bool) old('requires_prescription', true),
        'is_active' => (bool) old('is_active', true),
        'action' => '',
    ];
@endphp

@section('content')
    <div class="space-y-5">
        <x-page-header title="Data Obat" :description="$pageDescription">
            <x-slot:actions>
                @if ($canManage)
                    <x-button type="button" icon="plus" x-on:click="$dispatch('open-drug')">
                        Tambah Obat
                    </x-button>
                @endif
            </x-slot:actions>
        </x-page-header>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
            <x-stat-card label="Total Obat" :value="number_format($drugs->total(), 0, ',', '.')"
                         unit="item" tone="brand" icon="pill" compact />
            <x-stat-card label="Stok Menipis" :value="number_format($lowStockCount, 0, ',', '.')"
                         unit="item" tone="warning" icon="exclamation"
                         hint="Stok habis atau sudah mencapai stok minimum" compact />
            <x-stat-card label="Kategori" :value="number_format($categories->count(), 0, ',', '.')"
                         unit="kategori" tone="info" icon="cube"
                         hint="Golongan obat pada data yang tampil" compact />
        </div>

        <x-card padding="p-0">
            <x-filter-bar :action="route('master.drugs.index')" :reset="route('master.drugs.index')">
                <x-slot:fields>
                    <div>
                        <label for="q" class="field-label">Pencarian</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-ink-3">
                                <x-icon name="search" class="h-4 w-4" />
                            </span>
                            <input id="q" name="q" type="search" value="{{ $search }}"
                                   placeholder="Kode, nama, atau nama generik" class="field-control pl-9">
                        </div>
                    </div>

                    <div>
                        <label for="drug_category" class="field-label">Kategori</label>
                        <select id="drug_category" name="category" class="field-control">
                            <option value="">Semua kategori</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat }}" @selected($category === $cat)>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <span class="field-label">Stok</span>
                        <label for="low_stock" class="flex h-[38px] cursor-pointer select-none items-center gap-2.5 rounded-lg bg-surface px-3 ring-1 ring-line-2 ring-inset">
                            <input type="checkbox" id="low_stock" name="low_stock" value="1"
                                   @checked($lowStock)
                                   class="h-4 w-4 shrink-0 rounded border-line-2 accent-brand focus:ring-2 focus:ring-brand">
                            <span class="text-sm text-ink">Tampilkan stok menipis saja</span>
                        </label>
                    </div>
                </x-slot:fields>

                <x-slot:default>
                    <span class="text-xs text-ink-3">
                        Menampilkan {{ $drugs->firstItem() ?? 0 }}&ndash;{{ $drugs->lastItem() ?? 0 }}
                        dari {{ number_format($drugs->total(), 0, ',', '.') }} obat
                    </span>
                </x-slot:default>
            </x-filter-bar>

            <x-data-table :cols="$drugs->count()" min-width="64rem"
                          empty="Obat tidak ditemukan"
                          empty-message="Belum ada obat yang cocok dengan filter ini. Ubah pencarian atau tambahkan obat baru."
                          empty-icon="pill">
                <x-slot:emptyAction>
                    <x-button type="button" :href="$emptyCta['href']" :variant="$emptyCta['variant']" size="sm"
                              :icon="$emptyCta['icon']" :permission="$emptyCta['permission']"
                              x-on:click="{{ $emptyCta['click'] }}">
                        {{ $emptyCta['label'] }}
                    </x-button>
                </x-slot:emptyAction>

                <x-slot:head>
                    <tr>
                        <th scope="col">Kode</th>
                        <th scope="col">Nama Obat</th>
                        <th scope="col" class="hidden sm:table-cell">Kekuatan</th>
                        <th scope="col" class="hidden lg:table-cell">Kategori</th>
                        <th scope="col" class="text-right">Harga</th>
                        <th scope="col">Stok</th>
                        <th scope="col" class="hidden md:table-cell">Kedaluwarsa</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-right">Aksi</th>
                    </tr>
                </x-slot:head>

                <x-slot:body>
                    @foreach ($drugs as $drug)
                        <tr>
                            <td data-label="Kode">
                                <span class="font-mono text-xs font-bold text-ink">{{ $drug->code }}</span>
                            </td>

                            <td data-label="Nama Obat">
                                <span class="font-semibold text-ink">{{ $drug->display_name }}</span>
                                @if ($drug->generic_name)
                                    <span class="mt-0.5 block text-xs text-ink-3">{{ $drug->generic_name }}</span>
                                @endif
                            </td>

                            <td data-label="Kekuatan" class="hidden sm:table-cell">
                                <span class="text-xs text-ink-2">{{ $drug->strength ?: '-' }}</span>
                            </td>

                            <td data-label="Kategori" class="hidden lg:table-cell">
                                @if ($drug->category)
                                    <x-badge variant="slate">{{ $drug->category }}</x-badge>
                                @else
                                    <span class="text-xs text-ink-3">-</span>
                                @endif
                            </td>

                            <td data-label="Harga" class="text-right">
                                <span class="text-xs font-semibold text-ink tabular-nums">
                                    Rp {{ number_format($drug->price, 0, ',', '.') }}
                                </span>
                            </td>

                            <td data-label="Stok">
                                <x-badge :class="$drug->stockStatusBadgeClass()" dot>
                                    {{ number_format($drug->stock) }} {{ $drug->unit }}
                                </x-badge>
                                <span class="mt-0.5 block text-[11px] text-ink-3">{{ $drug->stockStatusLabel() }}</span>
                            </td>

                            <td data-label="Kedaluwarsa" class="hidden md:table-cell">
                                @if (! $drug->expired_at)
                                    <span class="text-xs text-ink-3">-</span>
                                @elseif ($drug->isExpired())
                                    <x-badge variant="danger" dot>Kedaluwarsa</x-badge>
                                @elseif ($drug->isExpiringSoon())
                                    <x-badge variant="warning" dot>Segera</x-badge>
                                @endif

                                @if ($drug->expired_at)
                                    <span class="mt-0.5 block font-mono text-[11px] text-ink-3">
                                        {{ $drug->expired_at->translatedFormat('d M Y') }}
                                    </span>
                                @endif
                            </td>

                            <td data-label="Status">
                                <div class="flex flex-wrap items-center gap-1">
                                    <x-active-badge :active="$drug->is_active" />
                                    @unless ($drug->requires_prescription)
                                        <x-badge variant="outline">Bebas</x-badge>
                                    @endunless
                                </div>
                            </td>

                            <td data-label="Aksi">
                                <div class="flex items-center justify-end gap-1">
                                    {{-- Penyesuaian stok (opname/koreksi) ditangani
                                         PharmacyController dan butuh izin farmasi,
                                         jadi tidak bergantung pada izin master. --}}
                                    @if ($canAdjustStock)
                                        <button type="button" class="btn btn-sm btn-ghost"
                                                title="Sesuaikan stok"
                                                aria-label="Sesuaikan stok {{ $drug->display_name }}"
                                                x-on:click="$dispatch('adjust-stock', @js([
                                                    'name' => $drug->display_name,
                                                    'current' => $drug->stock,
                                                    'unit' => $drug->unit,
                                                    'action' => route('pharmacy.drugs.stock', $drug),
                                                ]))">
                                            <x-icon name="cube" class="h-4 w-4" />
                                        </button>
                                    @endif

                                    @if ($canManage)
                                        <button type="button" class="btn btn-sm btn-secondary"
                                                    x-on:click="$dispatch('edit-drug', @js([
                                                        'id' => $drug->id,
                                                        'code' => $drug->code,
                                                        'name' => $drug->name,
                                                        'generic_name' => $drug->generic_name,
                                                        'category' => $drug->category,
                                                        'form' => $drug->form,
                                                        'strength' => $drug->strength,
                                                        'unit' => $drug->unit,
                                                        'price' => $drug->price,
                                                        'stock' => $drug->stock,
                                                        'min_stock' => $drug->min_stock,
                                                        'expired_at' => $drug->expired_at?->toDateString(),
                                                        'requires_prescription' => $drug->requires_prescription,
                                                        'is_active' => $drug->is_active,
                                                        'action' => route('master.drugs.update', $drug),
                                                    ]))">
                                            Ubah
                                        </button>

                                        <form method="POST" action="{{ route('master.drugs.destroy', $drug) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <x-button type="submit" size="sm" variant="danger" icon="trash"
                                                      x-on:click.prevent="$dispatch('confirm', {
                                                          action: $el.form.action,
                                                          method: 'DELETE',
                                                          title: 'Hapus obat {{ $drug->display_name }}?',
                                                          message: 'Obat {{ $drug->display_name }} akan dihapus permanen dari daftar obat. Obat yang sudah pernah masuk resep atau memiliki riwayat mutasi stok tidak dapat dihapus.',
                                                          confirmLabel: 'Hapus Obat',
                                                      })">
                                                Hapus
                                            </x-button>
                                        </form>
                                    @endif

                                    @unless ($canManage || $canAdjustStock)
                                        <span class="text-xs text-ink-3">&mdash;</span>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-slot:body>
            </x-data-table>

            @if ($drugs->hasPages())
                {{ $drugs->links() }}
            @endif
        </x-card>
    </div>

    {{-- ===================== Modal tambah / ubah obat ===================== --}}
    @if ($canManage)
        <div x-data="{
                open: false,
                editing: false,
                form: @js($drugForm),
                openCreate() {
                    this.editing = false;
                    this.form = Object.assign({}, @js($drugForm), { action: @js(route('master.drugs.store')) });
                    this.open = true;
                },
                openEdit(data) {
                    this.editing = true;
                    this.form = Object.assign({}, @js($drugForm), data);
                    this.open = true;
                },
            }"
             x-on:open-drug.window="openCreate()"
             x-on:edit-drug.window="openEdit($event.detail)">

            {{-- Judul modal tidak bisa bergantung pada state Alpine
                 (nilai atribut komponen dihitung di sisi PHP), jadi mode
                 formulir ditampilkan lewat badge di dalam bodi modal. --}}
            <x-modal title="Formulir Obat" subtitle="Data obat dipakai pada resep dan pengurangan stok"
                     icon="pill" maxWidth="3xl">
                <div class="mb-4 flex items-center gap-2.5">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-soft text-brand-ink">
                        <span x-show="editing"><x-icon name="pencil" class="h-4 w-4" /></span>
                        <span x-show="! editing" x-cloak><x-icon name="plus" class="h-4 w-4" /></span>
                    </span>
                    <p class="text-sm font-semibold text-ink"
                       x-text="editing ? 'Mengubah obat yang sudah ada' : 'Menambah obat baru ke master data'"></p>
                </div>

                <form :action="form.action" method="POST" class="space-y-4">
                    @csrf
                    <template x-if="editing">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <x-input name="code" label="Kode Obat" required :maxlength="30"
                                 placeholder="OB016"
                                 x-bind:value="form.code"
                                 x-on:input="$el.value = $el.value.toUpperCase().replace(/[^A-Z0-9_-]/g, '')" />

                        <div class="sm:col-span-2">
                            <x-input name="name" label="Nama Obat" required :maxlength="150"
                                     placeholder="Amoxicillin" x-bind:value="form.name" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <x-input name="generic_name" label="Nama Generik" :maxlength="150"
                                 placeholder="Amoksisilin" x-bind:value="form.generic_name" />

                        <x-input name="category" label="Kategori" :maxlength="60"
                                 placeholder="Antibiotik" x-bind:value="form.category"
                                 hint="Golongan obat, contoh: Antibiotik, Analgesik." />

                        <x-input name="form" label="Sediaan" :maxlength="50"
                                 placeholder="Tablet" x-bind:value="form.form" />
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <x-input name="strength" label="Kekuatan" :maxlength="30"
                                 placeholder="500 mg" x-bind:value="form.strength" />

                        <x-input name="unit" label="Satuan" required :maxlength="30"
                                 placeholder="Tablet" x-bind:value="form.unit" />

                        <x-input name="expired_at" label="Tanggal Kedaluwarsa" type="date"
                                 x-bind:value="form.expired_at"
                                 hint="Kosongkan bila kedaluwarsa tidak dicatat." />
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <x-input name="price" label="Harga" type="number" required min="0" max="100000000" step="1"
                                 prefix="Rp" x-bind:value="form.price" />

                        <x-input name="stock" label="Stok" type="number" required min="0" max="1000000" step="1"
                                 x-bind:value="form.stock" />

                        <x-input name="min_stock" label="Stok Minimum" type="number" required min="0" max="1000000" step="1"
                                 x-bind:value="form.min_stock"
                                 hint="Batas bawah sebelum obat ditandai menipis." />
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-checkbox name="requires_prescription" label="Wajib Resep"
                                    hint="Obat tanpa resep bebas dapat dibeli langsung."
                                    x-model="form.requires_prescription" />

                        <x-checkbox name="is_active" label="Obat Aktif"
                                    hint="Obat nonaktif tidak muncul pada pilihan resep."
                                    x-model="form.is_active" />
                    </div>

                    <x-slot:footer>
                        <button type="button" x-on:click="open = false" class="btn btn-md btn-secondary">Batal</button>
                        <button type="submit" class="btn btn-md btn-primary" x-text="editing ? 'Simpan Perubahan' : 'Tambah Obat'"></button>
                    </x-slot:footer>
                </form>
            </x-modal>
        </div>
    @endif

    {{-- ===================== Modal penyesuaian stok ===================== --}}
    @if ($canAdjustStock)
        <div x-data="{
                open: false,
                name: '',
                unit: '',
                current: 0,
                stock: 0,
                note: '',
                action: '',
                openAdjust(data) {
                    this.name = data.name;
                    this.unit = data.unit;
                    this.current = data.current;
                    this.stock = data.current;
                    this.note = '';
                    this.action = data.action;
                    this.open = true;
                },
                delta() {
                    return this.stock - this.current;
                },
            }"
             x-on:adjust-stock.window="openAdjust($event.detail)">

            <x-modal title="Sesuaikan Stok" subtitle="Untuk hasil opname atau koreksi pembukuan"
                     icon="cube" maxWidth="lg">
                <form :action="action" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="_method" value="PATCH">

                    <div class="rounded-lg bg-surface-2 px-4 py-3">
                        <p class="text-sm font-semibold text-ink" x-text="name"></p>
                        <p class="mt-0.5 text-xs text-ink-2">
                            Stok saat ini <span class="font-mono font-semibold" x-text="current"></span>
                            <span x-text="unit"></span>.
                           -Masukkan jumlah stok fisik yang terhitung, bukan selisihnya.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-input name="stock" label="Stok Baru" type="number" required min="0" max="1000000" step="1"
                                 x-model.number="stock"
                                 hint="Stok tidak boleh minus." />

                        <div class="flex flex-col justify-end pb-1">
                            <p class="text-xs text-ink-3">Selisih</p>
                            <p class="mt-1 font-mono text-sm font-semibold"
                               :class="delta() < 0 ? 'text-rose-600' : (delta() > 0 ? 'text-emerald-600' : 'text-ink-2')"
                               x-text="(delta() > 0 ? '+' : '') + delta() + ' ' + unit"></p>
                        </div>
                    </div>

                    <x-input name="note" label="Keterangan" maxlength="255"
                             x-model="note"
                             placeholder="Contoh: Hasil stock opname bulanan" />

                    <x-slot:footer>
                        <button type="button" x-on:click="open = false" class="btn btn-md btn-secondary">Batal</button>
                        <button type="submit" class="btn btn-md btn-primary" x-bind:disabled="stock === current">
                            Simpan Stok
                        </button>
                    </x-slot:footer>
                </form>
            </x-modal>
        </div>
    @endif
@endsection
