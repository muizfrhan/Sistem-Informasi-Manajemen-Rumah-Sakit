<script setup>
import { ref, nextTick, onMounted, onBeforeUnmount } from 'vue';

import AppIcon from './components/AppIcon.vue';
import SiteNavbar from './components/SiteNavbar.vue';
import HeroSection from './components/HeroSection.vue';
import TrustStrip from './components/TrustStrip.vue';
import FeaturesSection from './components/FeaturesSection.vue';
import ProductPreview from './components/ProductPreview.vue';
import HowItWorks from './components/HowItWorks.vue';
import RolesSection from './components/RolesSection.vue';
import SecuritySection from './components/SecuritySection.vue';
import AboutSection from './components/AboutSection.vue';
import DemoRequestSection from './components/DemoRequestSection.vue';
import FaqSection from './components/FaqSection.vue';
import CtaSection from './components/CtaSection.vue';
import SiteFooter from './components/SiteFooter.vue';

import { useReveal } from './composables/useReveal.js';
import { useActiveSection } from './composables/useActiveSection.js';
import { NAV_LINKS } from './data/content.js';

const props = defineProps({
    config: { type: Object, required: true },
});

// `atas` = hero. Termasuk di sini supaya item "Beranda" di navbar ikut
// menyala ketika user sedang di bagian atas halaman.
const SECTION_IDS = ['atas', 'fitur', 'preview', 'alur', 'peran', 'keamanan', 'kontak'];

const page = ref(null);
const { active } = useActiveSection(SECTION_IDS);
useReveal(page);

const toasts = ref([]);
let toastId = 0;

function pushToast({ type = 'success', text }) {
    const id = ++toastId;
    toasts.value.push({ id, type, text });
    setTimeout(() => dismissToast(id), 6000);
}

function dismissToast(id) {
    toasts.value = toasts.value.filter((t) => t.id !== id);
}

function scrollTo(id) {
    const el = document.getElementById(id);
    if (!el) return;
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const top = el.getBoundingClientRect().top + window.scrollY - 72;
    window.scrollTo({ top, behavior: reduce ? 'auto' : 'smooth' });
}

function goToDemo() {
    scrollTo('kontak');
    nextTick(() => document.getElementById('lp-demo-name')?.focus({ preventScroll: true }));
}

onMounted(() => {
    // Pesan sukses dari submit non-AJAX (fallback tanpa JavaScript).
    if (props.config.demoSuccess) {
        pushToast({ type: 'success', text: 'Permintaan demo tercatat.' });
    }
});

onBeforeUnmount(() => {
    toasts.value = [];
});
</script>

<template>
    <div ref="page" class="lp">
        <a href="#kontak" class="lp-skip">Lompat ke formulir</a>

        <SiteNavbar
            :links="NAV_LINKS"
            :login-url="config.loginUrl"
            :dashboard-url="config.dashboardUrl"
            :app-name="config.appName"
            :tagline="config.tagline"
            :active-id="active"
            @request-demo="goToDemo"
        />

        <main>
            <HeroSection :login-url="config.loginUrl" @explore="scrollTo('fitur')" />
            <TrustStrip />
            <FeaturesSection />
            <ProductPreview />
            <HowItWorks />
            <RolesSection />
            <SecuritySection />
            <AboutSection />

            <DemoRequestSection
                :action="config.demoAction"
                :csrf-token="config.csrfToken"
                :defaults="config.demoDefaults"
                :old-input="config.oldInput"
                :server-errors="config.serverErrors"
                :initial-success="config.demoSuccess"
                @toast="pushToast"
            />

            <FaqSection />
            <CtaSection :login-url="config.loginUrl" @request-demo="goToDemo" />
        </main>

        <SiteFooter
            :app-name="config.appName"
            :tagline="config.tagline"
            :login-url="config.loginUrl"
            :dashboard-url="config.dashboardUrl"
            :year="config.year"
            :contact="config.contact"
            @navigate="scrollTo"
            @request-demo="goToDemo"
        />

        <div class="lp-toast-wrap" role="status" aria-live="polite">
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="lp-toast"
                :class="toast.type === 'error' ? 'lp-toast--error' : ''"
            >
                <AppIcon
                    :name="toast.type === 'error' ? 'close' : 'check'"
                    :size="17"
                    style="flex: none; margin-top: 1px"
                />
                <span>{{ toast.text }}</span>
                <button
                    type="button"
                    class="lp-toast__close"
                    aria-label="Tutup notifikasi"
                    @click="dismissToast(toast.id)"
                >
                    <AppIcon name="close" :size="15" />
                </button>
            </div>
        </div>
    </div>
</template>

<style>
/* Skip link: hanya terlihat saat pengguna keyboard menekan Tab pertama kali. */
.lp-skip {
    position: absolute;
    left: -9999px;
    top: 0.5rem;
    z-index: 100;
    padding: 0.7rem 1.1rem;
    border-radius: var(--radius-pill);
    background: var(--white);
    color: var(--brand-700);
    font-size: 0.85rem;
    font-weight: 700;
    box-shadow: var(--shadow-md);
}

.lp-skip:focus {
    left: 1rem;
}
</style>
