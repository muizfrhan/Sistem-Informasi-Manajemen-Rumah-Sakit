<script setup>
import AppIcon from './AppIcon.vue';
import DemoRequestForm from './DemoRequestForm.vue';

const props = defineProps({
    action: { type: String, required: true },
    csrfToken: { type: String, required: true },
    defaults: { type: Object, default: () => ({}) },
    oldInput: { type: Object, default: () => ({}) },
    serverErrors: { type: Object, default: () => ({}) },
    initialSuccess: { type: Boolean, default: false },
});

const emit = defineEmits(['toast']);
</script>

<template>
    <section id="kontak" class="lp-section lp-section--tint" aria-labelledby="demo-title">
        <div class="lp-container">
            <div class="lp-preview">
                <div data-reveal="left">
                    <p class="lp-eyebrow">Permintaan demo</p>
                    <h2 id="demo-title" class="lp-h2">
                        Ingin melihat alur kerja sistem dengan kasus Anda sendiri?
                    </h2>
                    <p class="lp-lede">
                        Isi formulir berikut. Permintaan masuk ke daftar permintaan demo
                        di dalam aplikasi, lengkap dengan waktu dan alamat IP pemohon,
                        supaya administrator bisa menindaklanjuti.
                    </p>

                    <div class="lp-points" style="margin-top: 1.75rem">
                        <div class="lp-point">
                            <span class="lp-point__icon" aria-hidden="true">
                                <AppIcon name="shield" :size="15" />
                            </span>
                            <div>
                                <h3 class="lp-point__title">Formulir ini bukan pendaftaran akun</h3>
                                <p class="lp-point__body">
                                    Tidak ada akun yang dibuat dari sini. Akun pengguna hanya
                                    dibuat oleh administrator rumah sakit.
                                </p>
                            </div>
                        </div>

                        <div class="lp-point">
                            <span class="lp-point__icon" aria-hidden="true">
                                <AppIcon name="lock" :size="15" />
                            </span>
                            <div>
                                <h3 class="lp-point__title">Ada perlindungan spam</h3>
                                <p class="lp-point__body">
                                    Formulir dilindungi token CSRF, honeypot, dan pembatasan
                                    lima permintaan per menit per alamat IP.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="lp-card"
                    data-reveal="scale"
                    style="--reveal-delay: 120ms; padding: clamp(1.25rem, 2.5vw, 1.85rem)"
                >
                    <DemoRequestForm
                        :action="action"
                        :csrf-token="csrfToken"
                        :defaults="defaults"
                        :old-input="oldInput"
                        :server-errors="serverErrors"
                        :initial-success="initialSuccess"
                        @submitted="emit('toast', { type: 'success', text: 'Permintaan demo tercatat.' })"
                        @failed="(m) => emit('toast', { type: 'error', text: m })"
                    />
                </div>
            </div>
        </div>
    </section>
</template>
