import { ref, onMounted, onBeforeUnmount } from 'vue';

/**
 * Menandai section yang sedang terlihat, untuk state aktif di navbar.
 */
export function useActiveSection(ids) {
    const active = ref('');
    let observer = null;

    onMounted(() => {
        const sections = ids
            .map((id) => document.getElementById(id))
            .filter(Boolean);

        if (!sections.length || !('IntersectionObserver' in window)) return;

        observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) active.value = entry.target.id;
                });
            },
            // Area tengah viewport: section dianggap aktif saat user membacanya.
            { rootMargin: '-45% 0px -50% 0px', threshold: 0 }
        );

        sections.forEach((s) => observer.observe(s));
    });

    onBeforeUnmount(() => observer?.disconnect());

    return { active };
}
