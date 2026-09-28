@extends('layouts.app')

@section('title', 'ICD-10')
@section('heading', 'Kode ICD-10')
@section('subheading', $codes->total().' kode diagnosis')

@php
    $search = trim((string) request('q', ''));
    $category = trim((string) request('category', ''));
    $hasFilter = $search !== '' || $category !== '';

    $resetUrl = route('master.icd10.index');

    $emptyCta = $hasFilter
        ? ['label' => 'Reset Filter', 'href' => $resetUrl, 'variant' => 'secondary', 'icon' => 'refresh', 'permission' => null, 'click' => "location.href = '".$resetUrl."'"]
        : ['label' => 'Tambah Kode ICD-10', 'href' => null, 'variant' => 'primary', 'icon' => 'plus', 'permission' => 'master.manage', 'click' => "\$dispatch('open-icd10')"];

    $pageDescription = 'Total '.number_format($codes->total(), 0, ',', '.').' kode diagnosis'
        .($search !== '' ? ' untuk pencarian '.$search : '');

    $activeCount = $codes->getCollection()->where('is_active', true)->count();

    // Nilai awal form modal diambil dari old() agar input yang gagal
    // validasi tetap terisi kembali.
    $icdForm = [
        'code' => (string) old('code', ''),
        'description' => (string) old('description', ''),
        'category' => (string) old('category', ''),
        'is_active' => (bool) old('is_active', true),
        'action' => '',
    ];
@endphp

@section('content')
    <div class="space-y-5">
        <x-page-header title="Kode ICD-10" :description="$pageDescription">
            <x-slot:actions>
                @if ($canManage)
                    <x-button type="button" icon="plus" x-on:click="$dispatch('open-icd10')">
                        Tambah Kode
                    </x-button>
                @endif
            </x-slot:actions>
        </x-page-header>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
            <x-stat-card label="Total Kode" :value="number_format($codes->total(), 0, ',', '.')"
                         unit="kode" tone="brand" icon="document" compact />
            <x-stat-card label="Kode Aktif" :value="number_format($activeCount, 0, ',', '.')"
                         unit="kode" tone="success" icon="check-circle"
                         hint="Tersedia untuk dipilih pada diagnosa" compact />
            <x-stat-card label="Kategori" :value="number_format($categories->count(), 0, ',', '.')"
                         unit="kategori" tone="info" icon="cube"
                         hint="Kelompok bab ICD-10 yang terdaftar" compact />
        </div>

        <x-card padding="p-0">
            <x-filter-bar :action="$resetUrl" :reset="$resetUrl">
                <x-slot:fields>
                    <div>
                        <label for="q" class="field-label">Pencarian</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-ink-3">
                                <x-icon name="search" class="h-4 w-4" />
                            </span>
                            <input id="q" name="q" type="search" value="{{ $search }}"
                                   placeholder="Kode atau deskripsi diagnosis" class="field-control pl-9">
                        </div>
                    </div>

                    <div>
                        <label for="icd10_category" class="field-label">Kategori</label>
                        <select id="icd10_category" name="category" class="field-control">
                            <option value="">Semua kategori</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat }}" @selected($category === (string) $cat)>
                                    {{ \Illuminate\Support\Str::headline($cat) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </x-slot:fields>

                <x-slot:default>
                    <span class="text-xs text-ink-3">
                        Menampilkan {{ $codes->firstItem() ?? 0 }}&ndash;{{ $codes->lastItem() ?? 0 }}
                        dari {{ number_format($codes->total(), 0, ',', '.') }} kode
                    </span>
                </x-slot:default>
            </x-filter-bar>

            <x-data-table :cols="$codes->count()" min-width="52rem"
                          empty="Kode ICD-10 tidak ditemukan"
                          empty-message="Belum ada kode yang cocok dengan filter ini. Ubah kata kunci atau tambahkan kode diagnosis baru."
                          empty-icon="document">
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
                        <th scope="col">Deskripsi</th>
                        <th scope="col" class="hidden sm:table-cell">Kategori</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-right">Aksi</th>
                    </tr>
                </x-slot:head>

                <x-slot:body>
                    @foreach ($codes as $icd)
                        <tr>
                            <td data-label="Kode">
                                <span class="font-mono text-xs font-bold text-brand">{{ $icd->code }}</span>
                            </td>

                            <td data-label="Deskripsi">
                                <span class="text-ink-2">{{ $icd->description }}</span>
                            </td>

                            <td data-label="Kategori" class="hidden sm:table-cell">
                                @if ($icd->category)
                                    <x-badge variant="slate">
                                        {{ \Illuminate\Support\Str::headline($icd->category) }}
                                    </x-badge>
                                @else
                                    <span class="text-xs text-ink-3">-</span>
                                @endif
                            </td>

                            <td data-label="Status">
                                <x-active-badge :active="$icd->is_active" />
                            </td>

                            <td data-label="Aksi">
                                @if ($canManage)
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" class="btn btn-sm btn-secondary"
                                                x-on:click="$dispatch('edit-icd10', @js([
                                                    'id' => $icd->id,
                                                    'code' => $icd->code,
                                                    'description' => $icd->description,
                                                    'category' => $icd->category,
                                                    'is_active' => $icd->is_active,
                                                    'action' => route('master.icd10.update', $icd),
                                                ]))">
                                            Ubah
                                        </button>

                                        <form method="POST" action="{{ route('master.icd10.destroy', $icd) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <x-button type="submit" size="sm" variant="danger" icon="trash"
                                                      x-on:click.prevent="$dispatch('confirm', {
                                                          action: $el.form.action,
                                                          method: 'DELETE',
                                                          title: 'Hapus kode ICD-10 {{ $icd->code }}?',
                                                          message: 'Kode {{ $icd->code }} - {{ $icd->description }} akan dihapus permanen dari daftar diagnosis.',
                                                          confirmLabel: 'Hapus Kode',
                                                      })">
                                                Hapus
                                            </x-button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-ink-3">&mdash;</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </x-slot:body>
            </x-data-table>

            @if ($codes->hasPages())
                {{ $codes->links() }}
            @endif
        </x-card>
    </div>

    {{-- ===================== Modal tambah / ubah kode ICD-10 ===================== --}}
    @if ($canManage)
        <div x-data="{
                open: false,
                editing: false,
                form: @js($icdForm),
                openCreate() {
                    this.editing = false;
                    this.form = Object.assign({}, @js($icdForm), { action: @js(route('master.icd10.store')) });
                    this.open = true;
                },
                openEdit(data) {
                    this.editing = true;
                    this.form = Object.assign({}, @js($icdForm), data);
                    this.open = true;
                },
            }"
             x-on:open-icd10.window="openCreate()"
             x-on:edit-icd10.window="openEdit($event.detail)">

            {{-- Judul modal tidak bisa bergantung pada state Alpine
                 (nilai atribut komponen dihitung di sisi PHP), jadi mode
                 formulir ditampilkan lewat badge di dalam bodi modal. --}}
            <x-modal title="Formulir Kode ICD-10" subtitle="Referensi diagnosis internasional WHO ICD-10"
                     icon="document" maxWidth="lg">
                <div class="mb-4 flex items-center gap-2.5">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-soft text-brand-ink">
                        <span x-show="editing"><x-icon name="pencil" class="h-4 w-4" /></span>
                        <span x-show="! editing" x-cloak><x-icon name="plus" class="h-4 w-4" /></span>
                    </span>
                    <p class="text-sm font-semibold text-ink"
                       x-text="editing ? 'Mengubah kode diagnosis yang sudah ada' : 'Menambah kode diagnosis baru'"></p>
                </div>

                <form :action="form.action" method="POST" class="space-y-4">
                    @csrf
                    <template x-if="editing">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <x-input name="code" label="Kode ICD-10" required :maxlength="10"
                             placeholder="J00"
                             x-bind:value="form.code"
                             x-on:input="$el.value = $el.value.toUpperCase().replace(/[^A-Z0-9]/g, '')" />

                    <x-textarea name="description" label="Deskripsi Diagnosis" required :rows="2" :maxlength="255"
                                placeholder="Acute nasopharyngitis" x-bind:value="form.description" />

                    <x-input name="category" label="Kategori" :maxlength="10"
                             placeholder="J"
                             x-bind:value="form.category"
                             hint="Kode bab ICD-10, contoh: A sampai Z." />

                    <x-checkbox name="is_active" label="Kode Aktif"
                                hint="Kode nonaktif tidak muncul pada pilihan diagnosa."
                                x-model="form.is_active" />

                    <x-slot:footer>
                        <button type="button" x-on:click="open = false" class="btn btn-md btn-secondary">Batal</button>
                        <button type="submit" class="btn btn-md btn-primary" x-text="editing ? 'Simpan Perubahan' : 'Tambah Kode'"></button>
                    </x-slot:footer>
                </form>
            </x-modal>
        </div>
    @endif
@endsection
