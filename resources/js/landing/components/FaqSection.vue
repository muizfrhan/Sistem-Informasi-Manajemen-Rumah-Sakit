<script setup>
import { ref } from 'vue';
import AppIcon from './AppIcon.vue';
import { FAQ } from '../data/content.js';

// Hanya satu panel terbuka pada satu waktu supaya daftar tetap ringkas.
const open = ref(0);

function toggle(i) {
    open.value = open.value === i ? -1 : i;
}
</script>

<template>
    <section id="faq" class="lp-section lp-faq" aria-labelledby="faq-title">
        <div class="lp-container lp-container--narrow">
            <header class="lp-section__head lp-section__head--center" data-reveal>
                <p class="lp-eyebrow lp-eyebrow--center">Pertanyaan umum</p>
                <h2 id="faq-title" class="lp-h2">Hal yang paling sering ditanyakan</h2>
                <p class="lp-lede">
                    Jawaban di bawah hanya menjelaskan perilaku yang memang ada di
                    sistem. Fitur yang belum ada tidak dijadikan janji.
                </p>
            </header>

            <!-- Satu panel tunggal berisi seluruh item, dipisah garis tipis.
                 Dengan begitu pertanyaan dan jawabannya terlihat satu kesatuan,
                 bukan kartu-kartu yang menempel. -->
            <div class="lp-faq__list" data-reveal style="--reveal-delay: 80ms">
                <div
                    v-for="(item, i) in FAQ"
                    :key="item.q"
                    class="lp-faq__item"
                    :class="{ 'is-open': open === i }"
                >
                    <h3 class="lp-faq__heading">
                        <button
                            type="button"
                            class="lp-faq__q"
                            :aria-expanded="open === i"
                            :aria-controls="`lp-faq-panel-${i}`"
                            :id="`lp-faq-btn-${i}`"
                            @click="toggle(i)"
                        >
                            <span class="lp-faq__num" aria-hidden="true">
                                {{ String(i + 1).padStart(2, '0') }}
                            </span>
                            <span class="lp-faq__label">{{ item.q }}</span>
                            <span
                                class="lp-faq__chev"
                                :class="{ 'is-open': open === i }"
                                aria-hidden="true"
                            >
                                <AppIcon name="chevron-down" :size="18" />
                            </span>
                        </button>
                    </h3>

                    <!-- `inert` dipakai, bukan `hidden`: `hidden` memaksa
                         `display: none` sehingga transisi tinggi tidak jalan,
                         sedangkan `inert` menyembunyikan isi dari pembaca layar
                         dan urutan tab tanpa menyentuh layout sama sekali. -->
                    <div
                        :id="`lp-faq-panel-${i}`"
                        role="region"
                        :aria-labelledby="`lp-faq-btn-${i}`"
                        class="lp-faq__a"
                        :inert="open !== i"
                    >
                        <p>{{ item.a }}</p>
                    </div>
                </div>
            </div>

            <p class="lp-faq__foot" data-reveal>
                Belum menemukan jawabannya? Isi formulir permintaan demo, atau
                hubungi administrator sistem rumah sakit Anda.
            </p>
        </div>
    </section>
</template>

<style scoped>
/* Latar section dibuat lebih redup dari panel di dalamnya supaya blok
   pertanyaan terbaca sebagai satu kesatuan, bukan tumpukan kartu. */
.lp-faq {
    background: var(--surface-2);
    border-block: 1px solid var(--border);
}

.lp-faq__list {
    /* Tiga angka ini dipegang satu kali lalu dipakai baris pertanyaan maupun
       baris jawaban. Kolom nomor dibuat lebar tetap supaya teks jawaban bisa
       disejajarkan tepat di bawah teks pertanyaan tanpa menebak-nebak lebar
       isi nomor. */
    --faq-pad-x: 1.35rem;
    --faq-num-w: 1.4rem;
    --faq-col-gap: 1rem;
    --faq-text-x: calc(var(--faq-pad-x) + var(--faq-num-w) + var(--faq-col-gap));

    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.lp-faq__item + .lp-faq__item {
    border-top: 1px solid var(--border);
}

.lp-faq__heading {
    margin: 0;
    font-size: inherit;
    font-weight: inherit;
}

.lp-faq__q {
    display: grid;
    grid-template-columns: var(--faq-num-w) 1fr auto;
    align-items: center;
    column-gap: var(--faq-col-gap);
    width: 100%;
    padding: 1.15rem var(--faq-pad-x);
    background: none;
    border: 0;
    text-align: left;
    cursor: pointer;
    transition: background var(--dur) var(--ease-out);
}

.lp-faq__q:hover {
    background: var(--surface-2);
}

.lp-faq__item.is-open .lp-faq__q {
    background: var(--brand-50);
}

.lp-faq__num {
    width: var(--faq-num-w);
    font-family: var(--font-mono);
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--brand-400);
    transition: color var(--dur) var(--ease-out);
}

.lp-faq__item.is-open .lp-faq__num {
    color: var(--brand-600);
}

.lp-faq__label {
    font-size: 0.98rem;
    font-weight: 600;
    line-height: 1.45;
    letter-spacing: -0.01em;
    color: var(--text);
    text-wrap: pretty;
}

.lp-faq__q:hover .lp-faq__label,
.lp-faq__item.is-open .lp-faq__label {
    color: var(--brand-700);
}

.lp-faq__chev {
    display: grid;
    place-items: center;
    width: 30px;
    height: 30px;
    flex: none;
    border-radius: 50%;
    background: var(--surface-2);
    color: var(--muted);
    transition: transform var(--dur) var(--ease-out), background var(--dur) var(--ease-out),
        color var(--dur) var(--ease-out);
}

/* Berputar 180° ke depan, bukan ke belakang: chevron turun → chevron naik. */
.lp-faq__item.is-open .lp-faq__chev {
    transform: rotate(180deg);
    background: var(--white);
    color: var(--brand-600);
    box-shadow: var(--shadow-xs);
}

/* grid-template-rows 0fr → 1fr memberi transisi tinggi otomatis mengikuti isi,
   jadi jawaban yang lebih panjang tidak pernah terpotong oleh max-height tetap. */
.lp-faq__a {
    display: grid;
    grid-template-rows: 0fr;
    transition: grid-template-rows var(--dur-slow) var(--ease-out);
}

.lp-faq__item.is-open .lp-faq__a {
    grid-template-rows: 1fr;
}

.lp-faq__a > p {
    overflow: hidden;
    /* Padding kiri tetap sama saat terbuka maupun tertutup supaya baris tidak
       melompat saat panel dilepas. */
    padding-inline: var(--faq-text-x) var(--faq-pad-x);
    font-size: 0.925rem;
    line-height: 1.75;
    color: var(--muted);
    text-wrap: pretty;
}

.lp-faq__item.is-open .lp-faq__a > p {
    padding-bottom: 1.25rem;
}

.lp-faq__foot {
    margin-top: 1.5rem;
    text-align: center;
    font-size: 0.85rem;
    line-height: 1.6;
    color: var(--muted);
}

@media (max-width: 560px) {
    .lp-faq__list {
        --faq-pad-x: 1.05rem;
        --faq-num-w: 1.25rem;
        --faq-col-gap: 0.65rem;
    }

    .lp-faq__q {
        padding-block: 1rem;
    }

    .lp-faq__label {
        font-size: 0.94rem;
    }
}
</style>
