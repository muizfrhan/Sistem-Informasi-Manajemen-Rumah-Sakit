<script setup>
import AppIcon from './AppIcon.vue';
import { PREVIEW } from '../data/content.js';
</script>

<template>
    <section id="preview" class="lp-section lp-section--tint" aria-labelledby="preview-title">
        <div class="lp-container">
            <div class="lp-preview lp-preview--flip">
                <!-- Kolom teks -->
                <div data-reveal="left">
                    <p class="lp-eyebrow">Pratinjau</p>
                    <h2 id="preview-title" class="lp-h2">
                        Semua yang dibutuhkan untuk mengelola rumah sakit, dalam satu tampilan
                    </h2>
                    <p class="lp-lede">
                        Ringkasan operasional, antrean berjalan, dan aktivitas terbaru
                        disusun di satu halaman. Modul lain—farmasi, penagihan, rawat inap,
                        laporan—hanya terbuka bagi pengguna yang memang punya izinnya.
                    </p>

                    <p class="lp-point__body" style="margin-top: 1.5rem; display: flex; gap: 0.5rem; align-items: flex-start">
                        <AppIcon name="eye" :size="16" style="color: var(--brand-400); flex: none; margin-top: 2px" />
                        <span>
                            Angka pada halaman sungguhan dihitung langsung dari database.
                            Yang tampil di sini hanya kerangka tampilannya.
                        </span>
                    </p>
                </div>

                <!-- Mockup -->
                <div data-reveal="scale" style="--reveal-delay: 120ms">
                    <div class="lp-preview__mock">
                        <div class="lp-preview__chrome" aria-hidden="true">
                            <span class="lp-window__dot" style="background: #f87171"></span>
                            <span class="lp-window__dot" style="background: #fbbf24"></span>
                            <span class="lp-window__dot" style="background: #34d399"></span>
                            <span class="lp-window__url">/dashboard — pratinjau</span>
                        </div>

                        <div class="lp-preview__content">
                            <p
                                style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.1em;
                                       text-transform: uppercase; color: var(--muted)"
                            >
                                Mockup antarmuka — bukan data nyata
                            </p>

                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(8rem, 1fr)); gap: 0.75rem">
                                <div v-for="stat in PREVIEW.stats" :key="stat.label" class="lp-tile">
                                    <p class="lp-tile__label">{{ stat.label }}</p>
                                    <p class="lp-tile__value">{{ stat.value }}</p>
                                    <p class="lp-tile__delta">{{ stat.caption }}</p>
                                </div>
                            </div>

                            <div class="lp-tile" style="padding: 0.5rem 0.6rem">
                                <table class="lp-table">
                                    <caption style="text-align: left; padding: 0.35rem 0.6rem 0.5rem">
                                        <span class="lp-tile__label">Pendaftaran terbaru</span>
                                    </caption>
                                    <thead>
                                        <tr>
                                            <th scope="col">No. Daftar</th>
                                            <th scope="col">Poli</th>
                                            <th scope="col">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="row in PREVIEW.rows" :key="row.id">
                                            <td class="lp-table__id">{{ row.id }}</td>
                                            <td>{{ row.poly }}</td>
                                            <td>
                                                <span class="lp-pill" :class="`lp-pill--${row.tone}`">
                                                    {{ row.status }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="lp-tile">
                                <p class="lp-tile__label">Aktivitas terbaru</p>
                                <div style="display: grid; gap: 0.4rem; margin-top: 0.7rem">
                                    <div
                                        v-for="(row, i) in PREVIEW.activity"
                                        :key="row.title"
                                        style="display: flex; align-items: center; gap: 0.55rem;
                                               font-size: 0.82rem"
                                    >
                                        <span
                                            class="lp-row__avatar"
                                            style="background: var(--brand-50); color: var(--brand-600)"
                                        >{{ i + 1 }}</span>
                                        <span style="color: var(--text-soft)">{{ row.title }}</span>
                                        <span
                                            class="lp-row__meta"
                                            style="margin-left: auto; color: var(--muted)"
                                        >{{ row.meta }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="lp-legend">
                                <span><i style="background: var(--brand-500)"></i> Tersedia</span>
                                <span><i style="background: var(--accent-400)"></i> Dikerjakan</span>
                                <span><i style="background: #f2c078"></i> Menunggu</span>
                            </div>
                        </div>
                    </div>

                    <p
                        style="display: flex; gap: 0.45rem; align-items: center; margin-top: 0.9rem;
                               font-size: 0.76rem; color: var(--muted)"
                    >
                        <AppIcon name="eye" :size="14" style="color: var(--brand-400)" />
                        {{ PREVIEW.trendCaption }}
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>
