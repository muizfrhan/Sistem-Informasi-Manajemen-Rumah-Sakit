<script setup>
import AppIcon from './AppIcon.vue';
import { TRUST_ITEMS } from '../data/content.js';
</script>

<template>
    <!-- Strip pembuka: fakta keamanan yang bisa diverifikasi, bukan klaim performa. -->
    <section class="lp-strip" aria-label="Keamanan dan keandalan sistem">
        <div class="lp-container">
            <ul class="lp-strip__track">
                <li v-for="item in TRUST_ITEMS" :key="item.icon" class="lp-strip__item">
                    <AppIcon :name="item.icon" :size="17" />
                    {{ item.label }}
                </li>
            </ul>
        </div>
    </section>
</template>
