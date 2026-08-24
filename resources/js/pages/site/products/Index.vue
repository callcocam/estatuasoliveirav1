<script setup lang="ts">
import { InfiniteScroll, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import ProductCard from '@/components/site/ProductCard.vue';
import SectionHeading from '@/components/site/SectionHeading.vue';
import { useT } from '@/composables/useT';
import SiteLayout from '@/layouts/SiteLayout.vue';
import { index as productsIndex } from '@/routes/products';
import type { SiteCategorySummary, SiteProductCard } from '@/types/site';

const props = defineProps<{
    products: { data: SiteProductCard[] };
    categories: SiteCategorySummary[];
    filters: { categoria: string | null; busca: string | null };
}>();

const { t } = useT();

const search = ref(props.filters.busca ?? '');
let searchTimer: ReturnType<typeof setTimeout> | undefined;

watch(search, (value) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => applyFilters(props.filters.categoria, value), 400);
});

function applyFilters(categoria: string | null, busca: string | null): void {
    const query: Record<string, string> = {};

    if (categoria) {
        query.categoria = categoria;
    }

    if (busca) {
        query.busca = busca;
    }

    router.get(productsIndex({ query }).url, {}, { preserveState: true, preserveScroll: true, replace: true });
}
</script>

<template>
    <SiteLayout :title="t('app.site.meta.products_title')" :description="t('app.site.products.subtitle')">
        <div class="mx-auto max-w-6xl px-4 py-14 md:px-6 md:py-20">
            <SectionHeading :title="t('app.site.products.title')" :subtitle="t('app.site.products.subtitle')" />

            <!-- Filtros -->
            <div class="mb-10 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        :class="[
                            'rounded-site-badge px-4 py-2 text-sm transition-colors',
                            !filters.categoria
                                ? 'bg-site-secondary-container text-site-on-secondary-container'
                                : 'border border-site-outline-variant text-site-on-surface-variant hover:text-site-primary',
                        ]"
                        @click="applyFilters(null, search)"
                    >
                        {{ t('app.site.products.all_categories') }}
                    </button>
                    <button
                        v-for="category in categories"
                        :key="category.slug"
                        type="button"
                        :class="[
                            'rounded-site-badge px-4 py-2 text-sm transition-colors',
                            filters.categoria === category.slug
                                ? 'bg-site-secondary-container text-site-on-secondary-container'
                                : 'border border-site-outline-variant text-site-on-surface-variant hover:text-site-primary',
                        ]"
                        @click="applyFilters(category.slug, search)"
                    >
                        {{ category.name }}
                    </button>
                </div>

                <label class="relative block md:w-72">
                    <span class="sr-only">{{ t('app.site.products.search_label') }}</span>
                    <input
                        v-model="search"
                        type="search"
                        :placeholder="t('app.site.products.search_placeholder')"
                        class="w-full rounded-site border border-site-outline-variant bg-site-surface-container-lowest px-4 py-2.5 text-sm text-site-on-surface placeholder:text-site-on-surface-variant focus:border-site-primary focus:ring-1 focus:ring-site-primary focus:outline-none"
                    />
                </label>
            </div>

            <!-- Grade -->
            <InfiniteScroll v-if="products.data.length" data="products" :buffer="400">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <ProductCard v-for="product in products.data" :key="product.id" :product="product" />
                </div>

                <template #loading>
                    <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <div
                            v-for="index in 4"
                            :key="index"
                            class="aspect-[3/4] animate-pulse rounded-site-card bg-site-surface-container"
                        />
                    </div>
                </template>
            </InfiniteScroll>

            <!-- Estado vazio -->
            <div v-else class="rounded-site-card bg-site-surface-container-low px-6 py-20 text-center">
                <h2 class="font-display text-2xl text-site-on-surface">{{ t('app.site.products.empty_title') }}</h2>
                <p class="mt-3 text-sm text-site-on-surface-variant">{{ t('app.site.products.empty_text') }}</p>
                <Link
                    :href="productsIndex()"
                    class="mt-6 inline-block rounded-site bg-site-primary px-6 py-2.5 text-sm font-medium text-site-on-primary"
                >
                    {{ t('app.site.products.empty_clear') }}
                </Link>
            </div>
        </div>
    </SiteLayout>
</template>
