import './bootstrap';

import collapse from '@alpinejs/collapse';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.plugin(collapse);

/**
 * State sidebar bersama antara topbar (tombol buka) dan sidebar (drawer
 * mobile). Disimpan sebagai Alpine store agar keduanya selalu sinkron
 * tanpa saling mencari elemen DOM.
 */
Alpine.store('sidebar', {
    open: false,

    toggle() {
        this.open = !this.open;
    },

    close() {
        this.open = false;
    },
});

/**
 * Preferensi tampilan (mode gelap) disimpan agar pilihan pengguna bertahan
 * setelah halaman dimuat ulang. Inisialisasi dilakukan sebelum Alpine start
 * supaya tidak terjadi kedipan tema pada halaman yang lambat dimuat.
 */
const theme = (() => {
    try {
        return window.localStorage.getItem('simrs-theme') || 'system';
    } catch {
        return 'system';
    }
})();

const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
const resolved = theme === 'system' ? (prefersDark ? 'dark' : 'light') : theme;

document.documentElement.classList.toggle('dark', resolved === 'dark');
document.documentElement.style.colorScheme = resolved;

Alpine.store('theme', {
    current: theme,

    resolved,

    isDark() {
        return this.resolved === 'dark';
    },

    apply(value) {
        this.current = value;

        const next = value === 'system'
            ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
            : value;

        this.resolved = next;

        document.documentElement.classList.toggle('dark', next === 'dark');
        document.documentElement.style.colorScheme = next;

        try {
            window.localStorage.setItem('simrs-theme', value);
        } catch {
            /* penyimpanan tidak tersedia (mode privat) — abaikan */
        }
    },

    toggle() {
        this.apply(this.isDark() ? 'light' : 'dark');
    },
});

Alpine.start();
