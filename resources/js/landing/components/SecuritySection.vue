<script setup>
import AppIcon from './AppIcon.vue';
import { SECURITY } from '../data/content.js';
</script>

<template>
    <section id="keamanan" class="lp-section" aria-labelledby="keamanan-title">
        <div class="lp-container">
            <header class="lp-section__head lp-section__head--center" data-reveal>
                <p class="lp-eyebrow lp-eyebrow--center">Keamanan</p>
                <h2 id="keamanan-title" class="lp-h2">Keamanan yang benar-benar ada di kode</h2>
                <p class="lp-lede">
                    Setiap butir di bawah ini bisa dicek langsung pada berkas yang dicantumkan.
                    Fitur keamanan yang masih jadi rencana tidak ikut dicantumkan.
                </p>
            </header>

            <ul class="lp-grid lp-grid--3">
                <li
                    v-for="(item, i) in SECURITY"
                    :key="item.title"
                    class="lp-sec"
                    data-reveal
                    :style="`--reveal-delay: ${(i % 3) * 80}ms`"
                >
                    <span class="lp-sec__icon" aria-hidden="true">
                        <AppIcon :name="item.icon" :size="19" />
                    </span>
                    <div>
                        <h3 class="lp-sec__title">{{ item.title }}</h3>
                        <p class="lp-sec__body">{{ item.body }}</p>
                        <p class="lp-sec__proof">{{ item.proof }}</p>
                    </div>
                </li>
            </ul>
        </div>
    </section>
</template>
