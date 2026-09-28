{{--
    Dialog konfirmasi global.

    Ditempatkan satu kali di layout. Tombol pemicu cukup mengirim event
    `confirm` sehingga tabel dengan banyak baris tidak perlu memuat
    banyak dialog sekaligus.

    Contoh pemicu:
        <x-button x-on:click="$dispatch('confirm', {
            action: '{{ route('patients.destroy', $p) }}',
            method: 'DELETE',
            title: 'Hapus data pasien?',
            message: 'Tindakan ini tidak dapat dibatalkan.',
        })">Hapus</x-button>
--}}

<div
    x-data="{
        open: false,
        busy: false,
        action: '',
        method: 'POST',
        title: '',
        message: '',
        confirmLabel: 'Ya, Lanjutkan',
        tone: 'danger',
        ask(detail = {}) {
            this.action = detail.action ?? '';
            this.method = (detail.method ?? 'POST').toUpperCase();
            this.title = detail.title ?? 'Konfirmasi Tindakan';
            this.message = detail.message ?? 'Pastikan Anda yakin sebelum melanjutkan.';
            this.confirmLabel = detail.confirmLabel ?? 'Ya, Lanjutkan';
            this.tone = detail.tone ?? 'danger';
            this.busy = false;
            this.open = true;
        },
    }"
    x-on:confirm.window="ask($event.detail)"
    x-on:keydown.escape.window="if (open && ! busy) open = false"
    aria-live="polite"
>
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition-opacity ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[60] bg-slate-950/50 backdrop-blur-[2px]"
        x-on:click="if (! busy) open = false"
        aria-hidden="true"
    ></div>

    <div class="pointer-events-none fixed inset-0 z-[60] flex items-end justify-center p-4 sm:items-center">
        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95 sm:translate-y-0"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95 sm:translate-y-0"
            role="alertdialog"
            aria-modal="true"
            :aria-label="title"
            class="pointer-events-auto w-full max-w-md rounded-2xl bg-surface p-5 shadow-e3 ring-1 ring-line"
        >
            <div class="flex gap-4">
                <span
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full"
                    :class="tone === 'danger'
                        ? 'bg-danger-soft text-danger-ink'
                        : 'bg-info-soft text-info-ink'"
                >
                    <span x-show="tone !== 'danger'"><x-icon name="info" class="h-5 w-5" /></span>
                    <span x-show="tone === 'danger'" x-cloak><x-icon name="exclamation" class="h-5 w-5" /></span>
                </span>

                <div class="min-w-0 flex-1">
                    <h2 class="text-sm font-semibold text-ink" x-text="title"></h2>
                    <p class="mt-1.5 text-sm leading-relaxed text-ink-2" x-text="message"></p>
                </div>
            </div>

            <form method="POST" :action="action" x-on:submit="busy = true" class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                @csrf
                <template x-if="method !== 'POST'">
                    <input type="hidden" name="_method" :value="method">
                </template>

                <button type="button" x-on:click="open = false" x-bind:disabled="busy"
                        class="btn btn-md btn-secondary w-full sm:w-auto">
                    Batal
                </button>

                <button type="submit" x-bind:disabled="busy"
                        class="btn btn-md w-full sm:w-auto"
                        :class="tone === 'danger' ? 'btn-danger-solid' : 'btn-primary'"
                        x-text="busy ? 'Memproses...' : confirmLabel">
                </button>
            </form>
        </div>
    </div>
</div>
