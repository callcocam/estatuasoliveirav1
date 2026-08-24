<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useT } from '@/composables/useT';
import { show } from '@/routes/products';
import type { SiteProductCard } from '@/types/site';

defineProps<{ product: SiteProductCard }>();

const { t } = useT();
</script>

<template>
    <Link
        :href="show(product.slug)"
        class="group flex flex-col overflow-hidden rounded-site-card bg-site-surface-container-lowest shadow-sm transition-shadow duration-300 hover:shadow-md focus-visible:ring-2 focus-visible:ring-site-primary focus-visible:outline-none"
    >
        <div class="aspect-square overflow-hidden bg-site-surface-container">
            <img
                v-if="product.image"
                :src="product.image"
                :alt="product.name"
                loading="lazy"
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
            />
            <div
                v-else
                class="flex h-full w-full items-center justify-center text-site-outline"
            >
                <svg
                    class="h-12 w-12"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A1.75 1.75 0 0022 19.25V4.75A1.75 1.75 0 0020.25 3H3.75A1.75 1.75 0 002 4.75v14.5A1.75 1.75 0 003.75 21z"
                    />
                </svg>
            </div>
        </div>
        <div class="flex flex-1 flex-col gap-1 p-5">
            <p
                v-if="product.categoryName"
                class="text-xs tracking-widest text-site-on-surface-variant uppercase"
            >
                {{ product.categoryName }}
            </p>
            <h3
                class="font-display text-lg text-site-on-surface transition-colors group-hover:text-site-primary"
            >
                {{ product.name }}
            </h3>
            <p
                class="mt-auto flex flex-wrap gap-x-3 pt-1 text-sm text-site-on-surface-variant"
            >
                <span v-if="product.reference">{{
                    t('app.site.products.reference', {
                        reference: product.reference,
                    })
                }}</span>
                <span v-if="product.widthCm && product.heightCm">
                    {{
                        t('app.site.products.dimensions', {
                            width: product.widthCm,
                            height: product.heightCm,
                        })
                    }}
                </span>
            </p>
        </div>
    </Link>
</template>
