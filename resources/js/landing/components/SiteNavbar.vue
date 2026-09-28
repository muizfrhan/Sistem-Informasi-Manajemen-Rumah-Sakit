<script setup>
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import AppIcon from './AppIcon.vue';

const props = defineProps({
    links: { type: Array, required: true },
    loginUrl: { type: String, required: true },
    dashboardUrl: { type: String, default: null },
    appName: { type: String, required: true },
    tagline: { type: String, required: true },
    activeId: { type: String, default: '' },
});

const emit = defineEmits(['request-demo']);

// Diikat lewat variabel, bukan src literal: Vite memperlakukan `src="/..."`
// di template sebagai import dan gagal resolve-nya, karena berkas ini ada di
// public/ dan memang harus disajikan apa adanya.
const LOGO_MARK = '/assets/img/logo-mark.png';

const menuOpen = ref(false);
const scrolled = ref(false);

let ticking = false;

function onScroll() {
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(() => {
        scrolled.value = (window.scrollY || 0) > 24;
        ticking = false;
    });
}

function scrollTo(id) {
    menuOpen.value = false;
    const el = document.getElementById(id);
    if (!el) return;
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const top = el.getBoundingClientRect().top + window.scrollY - 72;
    window.scrollTo({ top, behavior: reduce ? 'auto' : 'smooth' });
    // Pindahkan fokus supaya keyboard user tidak kehilangan posisi.
    el.setAttribute('tabindex', '-1');
    nextTick(() => el.focus({ preventScroll: true }));
}

function onKeydown(e) {
    if (e.key === 'Escape' && menuOpen.value) menuOpen.value = false;
}

// Cegah scroll halaman saat menu mobile terbuka.
watch(menuOpen, (open) => {
    document.documentElement.style.overflow = open ? 'hidden' : '';
});

onMounted(() => {
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('keydown', onKeydown);
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', onScroll);
    window.removeEventListener('keydown', onKeydown);
    document.documentElement.style.overflow = '';
});
</script>

<template>
    <header class="lp-nav" :class="{ 'lp-nav--solid': scrolled }">
        <div class="lp-container lp-nav__inner">
            <a
                class="lp-nav__brand"
                href="#atas"
                :aria-label="`${appName} — kembali ke atas`"
                @click.prevent="scrollTo('atas')"
            >
                <span class="lp-nav__mark" aria-hidden="true">
                    <img
                        class="lp-nav__logo"
                        :src="LOGO_MARK"
                        alt=""
                        width="152"
                        height="152"
                        decoding="async"
                    />
                </span>
                <span>
                    <span class="lp-nav__title">{{ appName }}</span>
                    <span class="lp-nav__sub">{{ tagline }}</span>
                </span>
            </a>

            <nav class="lp-nav__links" aria-label="Navigasi utama">
                <a
                    v-for="link in links"
                    :key="link.id"
                    class="lp-nav__link"
                    :class="{ 'is-active': activeId === link.id }"
                    :href="`#${link.id}`"
                    :aria-current="activeId === link.id ? 'true' : undefined"
                    @click.prevent="scrollTo(link.id)"
                >{{ link.label }}</a>
            </nav>

            <div class="lp-nav__actions">
                <a
                    class="lp-btn lp-btn--sm lp-btn--onDark"
                    :href="dashboardUrl || loginUrl"
                >
                    {{ dashboardUrl ? 'Buka Dashboard' : 'Masuk' }}
                </a>
                <button
                    type="button"
                    class="lp-btn lp-btn--sm lp-btn--primary"
                    @click="emit('request-demo')"
                >
                    Jadwalkan Demo
                </button>
            </div>

            <button
                type="button"
                class="lp-nav__toggle"
                :aria-expanded="menuOpen"
                aria-controls="lp-mobile-nav"
                :aria-label="menuOpen ? 'Tutup menu navigasi' : 'Buka menu navigasi'"
                @click="menuOpen = !menuOpen"
            >
                <AppIcon :name="menuOpen ? 'close' : 'menu'" :size="22" />
            </button>
        </div>

        <div
            id="lp-mobile-nav"
            class="lp-nav__drawer"
            :style="menuOpen ? 'max-height: 32rem' : 'max-height: 0; opacity: 0'"
        >
            <nav class="lp-nav__drawer-nav" aria-label="Navigasi mobile">
                <a
                    v-for="link in links"
                    :key="link.id"
                    class="lp-nav__drawer-link"
                    :href="`#${link.id}`"
                    @click.prevent="scrollTo(link.id)"
                >{{ link.label }}</a>

                <div class="lp-nav__drawer-cta">
                    <a class="lp-btn lp-btn--block" :href="dashboardUrl || loginUrl">
                        {{ dashboardUrl ? 'Buka Dashboard' : 'Masuk ke Sistem' }}
                    </a>
                    <button
                        type="button"
                        class="lp-btn lp-btn--block lp-btn--ghost"
                        @click="menuOpen = false; emit('request-demo')"
                    >
                        Jadwalkan Demo
                    </button>
                </div>
            </nav>
        </div>
    </header>
</template>
