@props(['action' => null, 'method' => 'GET', 'reset' => null, 'sticky' => true])

{{--
    Bilah filter/pencarian untuk tabel daftar.
    Berisi form GET sehingga filter bekerja di sisi backend (bukan sekadar
    menyembunyikan baris di layar) dan hasilnya bisa dibagikan lewat URL.

    Slot `fields` berisi kontrol filter, slot `actions` tombol submit/reset.
--}}

<form method="GET" action="{{ $action }}" class="border-b border-line bg-surface-2/60">
    <div class="grid grid-cols-1 gap-3 p-4 sm:grid-cols-2 lg:grid-cols-4">
        {{ $fields }}
    </div>

    <div class="flex flex-wrap items-center gap-2 border-t border-line px-4 py-2.5">
        <x-button type="submit" size="sm" icon="funnel">Terapkan</x-button>

        @if ($reset)
            <x-button :href="$reset" size="sm" variant="ghost" icon="refresh">Reset</x-button>
        @endif

        <div class="ml-auto">{{ $slot }}</div>
    </div>
</form>
