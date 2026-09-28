<script setup>
import AppIcon from './AppIcon.vue';
import { ABOUT } from '../data/content.js';
</script>

<template>
    <section class="lp-section lp-section--tint" aria-labelledby="about-title">
        <div class="lp-container">
            <div class="lp-about">
                <div data-reveal="left">
                    <p class="lp-eyebrow">{{ ABOUT.eyebrow }}</p>
                    <h2 id="about-title" class="lp-h2">{{ ABOUT.title }}</h2>
                    <p class="lp-lede">{{ ABOUT.body }}</p>

                    <div class="lp-points">
                        <div
                            v-for="(point, i) in ABOUT.points"
                            :key="point.title"
                            class="lp-point"
                            data-reveal
                            :style="`--reveal-delay: ${i * 90}ms`"
                        >
                            <span class="lp-point__icon" aria-hidden="true">
                                <AppIcon :name="point.icon" :size="15" />
                            </span>
                            <div>
                                <h3 class="lp-point__title">{{ point.title }}</h3>
                                <p class="lp-point__body">{{ point.body }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div data-reveal="scale" style="--reveal-delay: 120ms">
                    <div class="lp-about__panel">
                        <p style="position: relative; z-index: 1; font-size: 0.72rem; font-weight: 700;
                                  letter-spacing: 0.1em; text-transform: uppercase; color: var(--muted-dark)">
                            Alur satu pasien
                        </p>

                        <div class="lp-flow" style="margin-top: 0.9rem">
                            <template v-for="(step, i) in ABOUT.flow" :key="step">
                                <div class="lp-flow__item">
                                    <span
                                        class="lp-role__initials"
                                        style="width: 26px; height: 26px; border-radius: 8px; font-size: 0.7rem"
                                    >{{ i + 1 }}</span>
                                    {{ step }}
                                    <AppIcon
                                        v-if="i < ABOUT.flow.length - 1"
                                        name="arrow"
                                        :size="15"
                                        class="lp-flow__arrow"
                                        style="transform: rotate(90deg)"
                                    />
                                </div>
                            </template>
                        </div>

                        <p style="position: relative; z-index: 1; margin-top: 1.1rem; font-size: 0.8rem;
                                  line-height: 1.6; color: var(--muted-dark)">
                            Satu nomor rekam medis menjadi kunci yang sama di seluruh langkah.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
