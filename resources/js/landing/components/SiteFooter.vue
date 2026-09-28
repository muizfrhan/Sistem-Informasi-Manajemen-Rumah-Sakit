<script setup>
import AppIcon from './AppIcon.vue';
import { NAV_LINKS } from '../data/content.js';

const props = defineProps({
    appName: { type: String, required: true },
    tagline: { type: String, required: true },
    loginUrl: { type: String, required: true },
    dashboardUrl: { type: String, default: null },
    year: { type: Number, required: true },
    contact: {
        type: Object,
        default: () => ({ address: '', phone: '', email: '' }),
    },
});

const emit = defineEmits(['navigate', 'request-demo']);

// Lihat catatan di SiteNavbar.vue: src literal di template diperlakukan Vite
// sebagai import, jadi URL-nya lewat variabel.
const LOGO_MARK = '/assets/img/logo-mark.png';

// Footer memakai daftar eksplisit, bukan pembalikan `!includes`, supaya
// item navbar yang baru (mis. "Beranda" -> hero) tidak otomatis bocor ke
// kolom "Sistem" di sini.
const FOOTER_FEATURE = ['fitur', 'preview', 'alur'];
const FOOTER_SYSTEM = ['peran', 'keamanan', 'kontak'];

const featureLinks = NAV_LINKS.filter((l) => FOOTER_FEATURE.includes(l.id));
const systemLinks = NAV_LINKS.filter((l) => FOOTER_SYSTEM.includes(l.id));
</script>

<template>
    <footer class="lp-footer">
        <div class="lp-container">
            <div class="lp-footer__grid">
                <div class="lp-footer__brandcol">
                    <span class="lp-nav__brand">
                        <span class="lp-nav__mark" aria-hidden="true">
                            <img
                                class="lp-nav__logo"
                                :src="LOGO_MARK"
                                alt=""
                                width="152"
                                height="152"
                                loading="lazy"
                                decoding="async"
                            />
                        </span>
                        <span>
                            <span class="lp-nav__title" style="color: #fff">{{ appName }}</span>
                            <span class="lp-nav__sub" style="color: var(--muted-dark)">{{ tagline }}</span>
                        </span>
                    </span>

                    <p style="margin-top: 1.1rem; font-size: 0.875rem; line-height: 1.7;
                              color: var(--muted-dark); max-width: 30rem">
                        Sistem informasi manajemen rumah sakit berbasis rekam medis elektronik.
                        Pendaftaran, antrean, rekam medis, farmasi, penagihan, dan rawat inap
                        berjalan di atas satu basis data yang sama.
                    </p>

                    <p style="margin-top: 1.25rem; display: flex; flex-wrap: wrap; gap: 0.4rem">
                        <span class="lp-chip" style="background: rgba(255,255,255,0.06);
                                     border-color: var(--border-dark); color: var(--on-brand-soft)">
                            7 peran akses
                        </span>
                        <span class="lp-chip" style="background: rgba(255,255,255,0.06);
                                     border-color: var(--border-dark); color: var(--on-brand-soft)">
                            22 izin granular
                        </span>
                        <span class="lp-chip" style="background: rgba(255,255,255,0.06);
                                     border-color: var(--border-dark); color: var(--on-brand-soft)">
                            Audit trail
                        </span>
                    </p>
                </div>

                <nav aria-label="Tautan modul">
                    <h2 class="lp-footer__title">Modul</h2>
                    <ul class="lp-footer__links">
                        <li v-for="l in featureLinks" :key="l.id">
                            <a :href="`#${l.id}`" @click.prevent="emit('navigate', l.id)">
                                {{ l.label }}
                            </a>
                        </li>
                    </ul>
                </nav>

                <nav aria-label="Tautan sistem">
                    <h2 class="lp-footer__title">Sistem</h2>
                    <ul class="lp-footer__links">
                        <li v-for="l in systemLinks" :key="l.id">
                            <a :href="`#${l.id}`" @click.prevent="emit('navigate', l.id)">
                                {{ l.label }}
                            </a>
                        </li>
                        <li>
                            <a :href="dashboardUrl || loginUrl">
                                {{ dashboardUrl ? 'Buka Dashboard' : 'Masuk ke Sistem' }}
                            </a>
                        </li>
                    </ul>
                </nav>

                <div>
                    <h2 class="lp-footer__title">Kontak</h2>
                    <ul class="lp-footer__links">
                        <li
                            v-if="contact.address"
                            style="display: flex; gap: 0.5rem; align-items: flex-start;
                                   font-size: 0.875rem; color: var(--muted-dark)"
                        >
                            <AppIcon name="box" :size="16" style="flex: none; margin-top: 3px" />
                            <span>{{ contact.address }}</span>
                        </li>
                        <li v-if="contact.phone">
                            <a :href="`tel:${contact.phone.replace(/[^\d+]/g, '')}`"
                               style="display: flex; gap: 0.5rem; align-items: center">
                                <AppIcon name="phone" :size="16" style="flex: none" />
                                {{ contact.phone }}
                            </a>
                        </li>
                        <li>
                            <button
                                type="button"
                                style="display: flex; gap: 0.5rem; align-items: center;
                                       font-size: 0.875rem; color: var(--muted-dark);
                                       background: none; border: 0; padding: 0; cursor: pointer"
                                @click="emit('request-demo')"
                            >
                                <AppIcon name="mail" :size="16" style="flex: none" />
                                Isi formulir permintaan demo
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="lp-footer__bottom">
                <p>&copy; {{ year }} {{ appName }}. Seluruh hak cipta dilindungi.</p>
                <p>Sistem Informasi Manajemen Rumah Sakit</p>
            </div>
        </div>
    </footer>
</template>
