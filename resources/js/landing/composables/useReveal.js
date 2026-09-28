import { onMounted, onBeforeUnmount } from 'vue';

/**
 * Menandai elemen sebagai "sudah tampil" saat masuk viewport.
 *
 * Memakai satu IntersectionObserver untuk seluruh elemen di halaman, bukan satu
 * observer per elemen — penting untuk halaman yang punya banyak kartu.
 * Otomatis dimatikan kalau pengguna memilih reduced motion (CSS sudah
 * memaksa opacity 1, jadi ini murni penghematan sumber daya).
 */
export function useReveal(root) {
    let observer = null;
    let nodes = [];

    const prefersReduced = () =>
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const collect = () => {
        const scope = root?.value ?? document;
        return Array.from(scope.querySelectorAll('[data-reveal]:not(.is-revealed)'));
    };

    const observe = () => {
        nodes = collect();
        if (!nodes.length) return;

        if (prefersReduced() || !('IntersectionObserver' in window)) {
            nodes.forEach((el) => el.classList.add('is-revealed'));
            return;
        }

        observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    entry.target.classList.add('is-revealed');
                    observer.unobserve(entry.target);
                });
            },
            { rootMargin: '0px 0px -8% 0px', threshold: 0.08 }
        );

        nodes.forEach((el) => observer.observe(el));
    };

    onMounted(() => {
        // Tick berikutnya supaya elemen yang baru dirender sudah ada di DOM.
        requestAnimationFrame(observe);
    });

    onBeforeUnmount(() => {
        observer?.disconnect();
        observer = null;
    });

    return { refresh: observe };
}
