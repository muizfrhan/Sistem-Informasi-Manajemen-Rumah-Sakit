import { createApp } from 'vue';
import LandingPage from './LandingPage.vue';

/**
 * Entry point landing page.
 *
 * Data diambil dari elemen #landing-config yang diisi Blade, supaya Laravel tetap
 * satu-satunya sumber kebenaran untuk URL, token CSRF, dan flash session.
 * Kalau elemen itu tidak ada (mis. halaman di-cache tanpa Blade), pakai
 * nilai cadangan yang hanya memuat URL statis.
 */
const el = document.getElementById('landing-app');
const cfgEl = document.getElementById('landing-config');

const fallback = {
    appName: 'SIMRS',
    tagline: 'Hospital Information System',
    loginUrl: '/login',
    dashboardUrl: null,
    demoAction: '/demo',
    csrfToken: '',
    year: new Date().getFullYear(),
    contact: {},
    demoDefaults: { facility_type: 'HOSPITAL' },
    oldInput: {},
    serverErrors: {},
    demoSuccess: false,
};

let config = { ...fallback };

if (cfgEl) {
    try {
        config = { ...fallback, ...JSON.parse(cfgEl.textContent || '{}') };
    } catch (error) {
        // JSON rusak tidak boleh membuat halaman kosong total.
        console.error('[landing] gagal membaca konfigurasi', error);
    }
}

createApp(LandingPage, { config }).mount(el);
