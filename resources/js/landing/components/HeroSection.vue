<script setup>
import AppIcon from './AppIcon.vue';
import { HERO } from '../data/content.js';

defineProps({
    loginUrl: { type: String, required: true },
});

defineEmits(['explore']);
</script>

<template>
    <!-- Hero full-bleed: background, overlay, dan dekorasi adalah layer absolut
         yang menempel ke seluruh viewport. Konten dibungkus container
        (max-width + padding) sendiri agar teks tidak menempel ke tepi. -->
    <section id="atas" class="lp-hero" aria-labelledby="hero-title">
        <div class="lp-hero__bg" aria-hidden="true"></div>

        <!-- Foto rumah sakit sebagai atmosfer, dipasang lewat CSS
             background (bukan <img>) supaya tidak terunduh di ponsel.
             Teks "SIMRS-RME" ikut tercetak di dalam berkasnya; lihat
             .lp-hero__photo di landing.css untuk cara constexpr-nya. -->
        <div class="lp-hero__photo" aria-hidden="true"></div>

        <div class="lp-hero__overlay" aria-hidden="true"></div>

        <div class="lp-hero__deco" aria-hidden="true">
            <span class="lp-hero__glow lp-hero__glow--1"></span>
            <span class="lp-hero__glow lp-hero__glow--2"></span>
            <span class="lp-hero__glow lp-hero__glow--3"></span>
            <span class="lp-hero__ring"></span>
            <span class="lp-hero__ring lp-hero__ring--2"></span>
        </div>

        <div class="lp-hero__container">
            <div class="lp-hero__grid">
            <div>
                <p class="lp-badge lp-badge--onDark" data-reveal>
                    <span class="lp-badge__dot" aria-hidden="true"></span>
                    {{ HERO.badge }}
                </p>

                <h1 id="hero-title" class="lp-hero__title" data-reveal style="--reveal-delay: 70ms">
                    {{ HERO.title }} <em>{{ HERO.titleAccent }}</em>
                </h1>

                <p class="lp-hero__lede" data-reveal style="--reveal-delay: 140ms">
                    {{ HERO.lede }}
                </p>

                <div class="lp-hero__cta" data-reveal style="--reveal-delay: 210ms">
                    <a class="lp-btn lp-btn--lg lp-btn--white" :href="loginUrl">
                        {{ HERO.primary }}
                        <AppIcon name="arrow" :size="18" class="lp-btn__icon" />
                    </a>
                    <button
                        type="button"
                        class="lp-btn lp-btn--lg lp-btn--onDark"
                        @click="$emit('explore')"
                    >
                        {{ HERO.secondary }}
                    </button>
                </div>

                <dl class="lp-hero__facts" data-reveal style="--reveal-delay: 280ms">
                    <div v-for="fact in HERO.facts" :key="fact.label" class="lp-hero__fact">
                        <dt>{{ fact.label }}</dt>
                        <dd>{{ fact.value }}</dd>
                    </div>
                </dl>

                <p class="lp-hero__note" data-reveal style="--reveal-delay: 330ms">
                    {{ HERO.note }}
                </p>
            </div>

            <!-- Mockup antarmuka. Nilai sengaja dibiarkan kosong: ini visual,
                 bukan data pasien nyata. -->
            <div class="lp-hero__visual" data-reveal="scale" style="--reveal-delay: 180ms">
                <div class="lp-window" role="img" aria-label="Pratinjau jendela dashboard sistem">
                    <div class="lp-window__bar">
                        <span class="lp-window__dot" style="background: #f87171"></span>
                        <span class="lp-window__dot" style="background: #fbbf24"></span>
                        <span class="lp-window__dot" style="background: #34d399"></span>
                        <span class="lp-window__url">/dashboard</span>
                    </div>

                    <div class="lp-window__body">
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.6rem">
                            <div
                                v-for="stat in ['Pasien hari ini', 'Menunggu antrean', 'Kamar terisi']"
                                :key="stat"
                                class="lp-stat"
                            >
                                <p class="lp-stat__label">{{ stat }}</p>
                                <p class="lp-skeleton" style="height: 20px; width: 60%; margin-top: 0.45rem"></p>
                            </div>
                        </div>

                        <div class="lp-stat">
                            <p class="lp-stat__label">Tren kunjungan</p>
                            <div class="lp-trend" style="margin-top: 0.6rem">
                                <span
                                    v-for="(h, i) in [40, 62, 48, 80, 55, 70, 52]"
                                    :key="i"
                                    class="lp-trend__bar"
                                    :style="{ height: h + '%', animationDelay: i * 70 + 'ms' }"
                                ></span>
                            </div>
                        </div>

                        <div class="lp-stat">
                            <p class="lp-stat__label">Antrean berjalan</p>
                            <div style="display: grid; gap: 0.4rem; margin-top: 0.6rem">
                                <div v-for="r in 3" :key="r" class="lp-row">
                                    <span class="lp-row__avatar">{{ ['A', 'B', 'C'][r - 1] }}</span>
                                    <span class="lp-row__name">Pelanggan</span>
                                    <span class="lp-row__meta">#{{ 10 + r }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lp-float lp-float--1" aria-hidden="true">
                    <span class="lp-float__icon"><AppIcon name="lock" :size="15" /></span>
                    NIK terenkripsi
                </div>
                <div class="lp-float lp-float--2" aria-hidden="true">
                    <span class="lp-float__icon"><AppIcon name="shield" :size="15" /></span>
                    22 izin diperiksa
                </div>
            </div>
            </div>
        </div>
    </section>
</template>
