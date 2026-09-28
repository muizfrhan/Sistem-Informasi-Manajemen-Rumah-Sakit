<script setup>
import { HOW_IT_WORKS } from '../data/content.js';
</script>

<template>
    <section id="alur" class="lp-section" aria-labelledby="alur-title">
        <div class="lp-container">
            <header class="lp-section__head" data-reveal>
                <p class="lp-eyebrow">How it works</p>
                <h2 id="alur-title" class="lp-h2">Empat langkah dari akun sampai laporan</h2>
                <p class="lp-lede">
                    Alur yang benar-benar dijalankan pengguna setiap hari, bukan alur orientasi
                    yang idealis.
                </p>
            </header>

            <ol class="lp-steps lp-steps--row">
                <li
                    v-for="(step, i) in HOW_IT_WORKS"
                    :key="step.title"
                    class="lp-step"
                    data-reveal
                    :style="`--reveal-delay: ${i * 90}ms`"
                >
                    <span class="lp-step__num" aria-hidden="true">
                        {{ String(i + 1).padStart(2, '0') }}
                    </span>
                    <div>
                        <h3 class="lp-step__title">{{ step.title }}</h3>
                        <p class="lp-step__body">{{ step.body }}</p>
                        <ul class="lp-step__tags">
                            <li v-for="tag in step.tags" :key="tag" class="lp-chip">
                                {{ tag }}
                            </li>
                        </ul>
                    </div>
                </li>
            </ol>
        </div>
    </section>
</template>
