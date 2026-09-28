<script setup>
import { ROLES } from '../data/content.js';
</script>

<template>
    <section id="peran" class="lp-section lp-section--dark" aria-labelledby="peran-title">
        <span aria-hidden="true" style="position: absolute; inset: 0; pointer-events: none;
              background: radial-gradient(ellipse 60% 50% at 80% 0%, rgba(13, 154, 169, 0.16), transparent 70%)"></span>

        <div class="lp-container" style="position: relative">
            <header class="lp-section__head" data-reveal>
                <p class="lp-eyebrow">Peran pengguna</p>
                <h2 id="peran-title" class="lp-h2">
                    Tujuh peran, tujuh cakupan akses yang berbeda
                </h2>
                <p class="lp-lede">
                    Cakupan akses berasal dari peran yang ditetapkan administrator, bukan dari
                    pilihan di layar. Menu yang tidak boleh diakses juga tidak dirender.
                </p>
            </header>

            <ul class="lp-grid lp-grid--3 lp-roles">
                <li
                    v-for="(role, i) in ROLES"
                    :key="role.name"
                    class="lp-role"
                    data-reveal
                    :style="`--reveal-delay: ${(i % 3) * 80}ms`"
                >
                    <span class="lp-role__initials" aria-hidden="true">{{ role.initials }}</span>
                    <div>
                        <h3 class="lp-role__name">{{ role.name }}</h3>
                        <p class="lp-role__desc">{{ role.desc }}</p>
                        <p class="lp-role__count">{{ role.count }}</p>
                    </div>
                </li>
            </ul>
        </div>
    </section>
</template>
