<script setup>
import AppIcon from './AppIcon.vue';
import { FEATURES } from '../data/content.js';
</script>

<template>
    <section id="fitur" class="lp-section" aria-labelledby="fitur-title">
        <div class="lp-container">
            <header class="lp-section__head lp-section__head--center" data-reveal>
                <p class="lp-eyebrow lp-eyebrow--center">Modul</p>
                <h2 id="fitur-title" class="lp-h2">
                    Setiap modul yang ada, dan tidak ada yang dijanjikan di luar itu
                </h2>
                <p class="lp-lede">
                    Semua modul di bawah ini punya route, controller, dan halaman di aplikasi.
                    Yang belum ada di backend tidak dicantumkan di sini.
                </p>
            </header>

            <ul class="lp-grid lp-grid--3">
                <li
                    v-for="(feature, i) in FEATURES"
                    :key="feature.title"
                    class="lp-card lp-card--hover"
                    data-reveal
                    :style="`--reveal-delay: ${(i % 3) * 80}ms`"
                >
                    <span class="lp-card__icon" aria-hidden="true">
                        <AppIcon :name="feature.icon" :size="22" />
                    </span>
                    <h3 class="lp-card__title">{{ feature.title }}</h3>
                    <p class="lp-card__body">{{ feature.body }}</p>

                    <ul style="display: grid; gap: 0.35rem; margin-top: 1rem">
                        <li
                            v-for="point in feature.points"
                            :key="point"
                            style="display: flex; gap: 0.45rem; align-items: flex-start;
                                   font-size: 0.8rem; color: var(--muted); line-height: 1.5"
                        >
                            <AppIcon
                                name="check"
                                :size="14"
                                style="color: var(--accent-500); flex: none; margin-top: 3px"
                            />
                            <span>{{ point }}</span>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </section>
</template>
