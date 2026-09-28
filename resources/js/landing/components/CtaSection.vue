<script setup>
import AppIcon from './AppIcon.vue';
import { CTA } from '../data/content.js';

defineProps({
    loginUrl: { type: String, required: true },
});

defineEmits(['request-demo']);
</script>

<template>
    <section class="lp-section" aria-labelledby="cta-title">
        <div class="lp-container">
            <div class="lp-cta" data-reveal="scale">
                <h2 id="cta-title" class="lp-cta__title">{{ CTA.title }}</h2>
                <p class="lp-cta__body">{{ CTA.body }}</p>

                <div class="lp-cta__actions">
                    <a class="lp-btn lp-btn--lg lp-btn--white" :href="loginUrl">
                        {{ CTA.primary }}
                        <AppIcon name="arrow" :size="18" class="lp-btn__icon" />
                    </a>
                    <button
                        type="button"
                        class="lp-btn lp-btn--lg lp-btn--onDark"
                        @click="$emit('request-demo')"
                    >
                        {{ CTA.secondary }}
                    </button>
                </div>

                <p class="lp-cta__note">{{ CTA.note }}</p>
            </div>
        </div>
    </section>
</template>
