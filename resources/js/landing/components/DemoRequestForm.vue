<script setup>
import { ref, reactive, computed } from 'vue';
import AppIcon from './AppIcon.vue';

const props = defineProps({
    action: { type: String, required: true },
    csrfToken: { type: String, required: true },
    defaults: { type: Object, default: () => ({}) },
    oldInput: { type: Object, default: () => ({}) },
    serverErrors: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['submitted', 'failed']);

const form = reactive({
    name: props.oldInput.name ?? props.defaults.name ?? '',
    position: props.oldInput.position ?? props.defaults.position ?? '',
    institution: props.oldInput.institution ?? props.defaults.institution ?? '',
    email: props.oldInput.email ?? props.defaults.email ?? '',
    phone: props.oldInput.phone ?? props.defaults.phone ?? '',
    facility_type: props.oldInput.facility_type ?? props.defaults.facility_type ?? 'HOSPITAL',
    bed_count: props.oldInput.bed_count ?? props.defaults.bed_count ?? '',
    message: props.oldInput.message ?? props.defaults.message ?? '',
    // Honeypot: tidak pernah diisi manusia. Kalau terisi, server menolak.
    company_website: '',
});

const errors = reactive({ ...props.serverErrors });
const submitting = ref(false);
const requestFailed = ref(false);
const done = ref(false);

const FACILITIES = [
    { value: 'HOSPITAL', label: 'Rumah Sakit' },
    { value: 'CLINIC', label: 'Klinik' },
    { value: 'PRIVATE_PRACTICE', label: 'Praktik Mandiri' },
    { value: 'OTHER', label: 'Lainnya' },
];

const hasError = (field) => Boolean(errors[field]);

function errorFor(field) {
    const value = errors[field];
    if (!value) return '';
    return Array.isArray(value) ? value[0] : String(value);
}

/** Kirim lewat fetch supaya bisa menampilkan status tanpa pindah halaman.
 *  Kalau fetch gagal (timeout/offline), form tetap punya aksi native sebagai
 *  cadangan: method POST di elemen form. */
async function onSubmit() {
    submitting.value = true;
    requestFailed.value = false;
    done.value = false;

    try {
        const response = await fetch(props.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': props.csrfToken,
            },
            credentials: 'same-origin',
            body: JSON.stringify(form),
        });

        if (response.ok) {
            done.value = true;
            emit('submitted');
            return;
        }

        const payload = await response.json().catch(() => ({}));

        if (payload?.errors && typeof payload.errors === 'object') {
            Object.keys(errors).forEach((k) => delete errors[k]);
            Object.assign(errors, payload.errors);
        } else if (payload?.message) {
            requestFailed.value = true;
            emit('failed', payload.message);
        } else {
            requestFailed.value = true;
        }
    } catch {
        requestFailed.value = true;
        emit('failed', 'Permintaan gagal dikirim. Silakan coba lagi.');
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <form
        :action="action"
        method="POST"
        class="lp-form"
        novalidate
        @submit.prevent="onSubmit"
    >
        <input type="hidden" name="_token" :value="csrfToken" />
        <input type="hidden" name="redirect" value="1" />

        <div v-if="done" class="lp-alert lp-alert--success" role="status">
            <AppIcon name="check" :size="18" style="flex: none; margin-top: 2px" />
            <span>
                Permintaan demo sudah tercatat. Administrator sistem akan menindaklanjuti
                dari daftar permintaan demo.
            </span>
        </div>

        <div v-if="requestFailed" class="lp-alert lp-alert--error" role="alert">
            <AppIcon name="close" :size="18" style="flex: none; margin-top: 2px" />
            <span>
                Permintaan tidak terkirim. Anda bisa mengisi ulang formulir, atau
                menghubungi administrator sistem secara langsung.
            </span>
        </div>

        <div class="lp-form__row">
            <div class="lp-field">
                <label class="lp-field__label" for="lp-demo-name">
                    Nama lengkap <span aria-hidden="true">*</span>
                </label>
                <input
                    id="lp-demo-name"
                    v-model="form.name"
                    name="name"
                    type="text"
                    class="lp-field__control"
                    autocomplete="name"
                    maxlength="150"
                    required
                    placeholder="Nama Anda"
                    :aria-invalid="hasError('name')"
                    :aria-describedby="hasError('name') ? 'lp-err-name' : undefined"
                >
                <p v-if="hasError('name')" id="lp-err-name" class="lp-field__error">
                    {{ errorFor('name') }}
                </p>
            </div>

            <div class="lp-field">
                <label class="lp-field__label" for="lp-demo-position">Jabatan</label>
                <input
                    id="lp-demo-position"
                    v-model="form.position"
                    name="position"
                    type="text"
                    class="lp-field__control"
                    maxlength="100"
                    placeholder="Direktur / Kepala IT"
                    :aria-invalid="hasError('position')"
                    :aria-describedby="hasError('position') ? 'lp-err-position' : undefined"
                >
                <p v-if="hasError('position')" id="lp-err-position" class="lp-field__error">
                    {{ errorFor('position') }}
                </p>
            </div>
        </div>

        <div class="lp-field">
            <label class="lp-field__label" for="lp-demo-institution">
                Nama rumah sakit / klinik <span aria-hidden="true">*</span>
            </label>
            <input
                id="lp-demo-institution"
                v-model="form.institution"
                name="institution"
                type="text"
                class="lp-field__control"
                maxlength="200"
                required
                placeholder="Contoh: RS Umum Harapan Bangsa"
                :aria-invalid="hasError('institution')"
                :aria-describedby="hasError('institution') ? 'lp-err-institution' : undefined"
            >
            <p v-if="hasError('institution')" id="lp-err-institution" class="lp-field__error">
                {{ errorFor('institution') }}
            </p>
        </div>

        <div class="lp-form__row">
            <div class="lp-field">
                <label class="lp-field__label" for="lp-demo-email">
                    Email kantor <span aria-hidden="true">*</span>
                </label>
                <input
                    id="lp-demo-email"
                    v-model="form.email"
                    name="email"
                    type="email"
                    class="lp-field__control"
                    autocomplete="email"
                    maxlength="150"
                    required
                    placeholder="nama@rumahsakit.co.id"
                    :aria-invalid="hasError('email')"
                    :aria-describedby="hasError('email') ? 'lp-err-email' : undefined"
                >
                <p v-if="hasError('email')" id="lp-err-email" class="lp-field__error">
                    {{ errorFor('email') }}
                </p>
            </div>

            <div class="lp-field">
                <label class="lp-field__label" for="lp-demo-phone">
                    Nomor WhatsApp / telepon <span aria-hidden="true">*</span>
                </label>
                <input
                    id="lp-demo-phone"
                    v-model="form.phone"
                    name="phone"
                    type="tel"
                    class="lp-field__control"
                    autocomplete="tel"
                    maxlength="30"
                    required
                    placeholder="0812xxxxxxx"
                    :aria-invalid="hasError('phone')"
                    :aria-describedby="hasError('phone') ? 'lp-err-phone' : undefined"
                >
                <p v-if="hasError('phone')" id="lp-err-phone" class="lp-field__error">
                    {{ errorFor('phone') }}
                </p>
            </div>
        </div>

        <div class="lp-form__row">
            <div class="lp-field">
                <label class="lp-field__label" for="lp-demo-facility">
                    Jenis fasilitas <span aria-hidden="true">*</span>
                </label>
                <select
                    id="lp-demo-facility"
                    v-model="form.facility_type"
                    name="facility_type"
                    class="lp-field__control"
                    required
                    :aria-invalid="hasError('facility_type')"
                >
                    <option v-for="f in FACILITIES" :key="f.value" :value="f.value">
                        {{ f.label }}
                    </option>
                </select>
                <p v-if="hasError('facility_type')" class="lp-field__error">
                    {{ errorFor('facility_type') }}
                </p>
            </div>

            <div class="lp-field">
                <label class="lp-field__label" for="lp-demo-bed">Jumlah tempat tidur</label>
                <input
                    id="lp-demo-bed"
                    v-model="form.bed_count"
                    name="bed_count"
                    type="number"
                    class="lp-field__control"
                    min="0"
                    max="5000"
                    placeholder="100"
                    :aria-invalid="hasError('bed_count')"
                    :aria-describedby="hasError('bed_count') ? 'lp-err-bed' : undefined"
                >
                <p v-if="hasError('bed_count')" id="lp-err-bed" class="lp-field__error">
                    {{ errorFor('bed_count') }}
                </p>
            </div>
        </div>

        <div class="lp-field">
            <label class="lp-field__label" for="lp-demo-message">Kebutuhan Anda</label>
            <textarea
                id="lp-demo-message"
                v-model="form.message"
                name="message"
                class="lp-field__control"
                rows="3"
                maxlength="2000"
                placeholder="Contoh: Kami masih memakai pencatatan manual, sulit menyusun laporan harian, dan butuh integrasi dengan apotek."
                :aria-invalid="hasError('message')"
                :aria-describedby="hasError('message') ? 'lp-err-message' : undefined"
            ></textarea>
            <p v-if="hasError('message')" id="lp-err-message" class="lp-field__error">
                {{ errorFor('message') }}
            </p>
        </div>

        <!-- Honeypot: disembunyikan, tapi tetap dibaca peramban dan pembaca layar. -->
        <div class="lp-honeypot" aria-hidden="true">
            <label for="lp-demo-website">Website perusahaan</label>
            <input
                id="lp-demo-website"
                v-model="form.company_website"
                name="company_website"
                type="text"
                tabindex="-1"
                autocomplete="off"
            >
        </div>

        <button
            type="submit"
            class="lp-btn lp-btn--lg lp-btn--primary lp-btn--block"
            :disabled="submitting"
            :aria-busy="submitting"
        >
            <span v-if="submitting" class="lp-spinner" aria-hidden="true"></span>
            {{ submitting ? 'Mengirim…' : 'Kirim Permintaan Demo' }}
        </button>

        <p class="lp-field__hint" style="text-align: center">
            Permintaan dicatat bersama waktu dan alamat IP pemohon. Formulir ini bukan
            pendaftaran akun.
        </p>
    </form>
</template>
