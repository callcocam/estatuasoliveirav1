import { computed } from 'vue';
import type { Paginated } from '@/types/admin';

/**
 * Wraps a deferred paginator prop (undefined while Inertia loads it) and
 * exposes loading state plus safe accessors for the list page.
 */
export function useDeferredPaginator<T>(
    source: () => Paginated<T> | undefined,
) {
    const paginator = computed(source);
    const isLoading = computed(() => paginator.value === undefined);
    const rows = computed<T[]>(() => paginator.value?.data ?? []);
    const links = computed(() => paginator.value?.links ?? []);
    const total = computed(() => paginator.value?.total ?? 0);
    const isEmpty = computed(() => !isLoading.value && rows.value.length === 0);

    return { paginator, isLoading, isEmpty, rows, links, total };
}
