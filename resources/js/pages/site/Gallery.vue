<script setup lang="ts">
import { InfiniteScroll, Link } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import SectionHeading from '@/components/site/SectionHeading.vue';
import { useT } from '@/composables/useT';
import SiteLayout from '@/layouts/SiteLayout.vue';
import { show as productShow } from '@/routes/products';
import type { SiteGalleryImage } from '@/types/site';

defineProps<{
    images: { data: SiteGalleryImage[] };
}>();

const { t } = useT();

const lightboxImage = ref<SiteGalleryImage | null>(null);

function handleKeydown(event: KeyboardEvent): void {
    if (event.key === 'Escape') {
        lightboxImage.value = null;
    }
}

onMounted(() => window.addEventListener('keydown', handleKeydown));
onBeforeUnmount(() => window.removeEventListener('keydown', handleKeydown));
</script>

<template>
    <SiteLayout
        :title="t('app.site.meta.gallery_title')"
        :description="t('app.site.gallery.subtitle')"
    >
        <div class="mx-auto max-w-6xl px-4 py-14 md:px-6 md:py-20">
            <SectionHeading
                :title="t('app.site.gallery.title')"
                :subtitle="t('app.site.gallery.subtitle')"
                align="center"
            />

            <InfiniteScroll
                v-if="images.data.length"
                data="images"
                :buffer="400"
            >
                <div
                    class="columns-2 gap-4 md:columns-3 lg:columns-4 [&>button]:mb-4"
                >
                    <button
                        v-for="image in images.data"
                        :key="image.id"
                        type="button"
                        class="block w-full overflow-hidden rounded-site-card focus-visible:ring-2 focus-visible:ring-site-primary focus-visible:outline-none"
                        @click="lightboxImage = image"
                    >
                        <img
                            :src="image.url"
                            :alt="
                                image.productName ?? t('app.site.gallery.title')
                            "
                            loading="lazy"
                            class="w-full transition-transform duration-500 hover:scale-105"
                        />
                    </button>
                </div>

                <template #loading>
                    <div class="mt-4 grid grid-cols-2 gap-4 md:grid-cols-4">
                        <div
                            v-for="index in 4"
                            :key="index"
                            class="aspect-square animate-pulse rounded-site-card bg-site-surface-container"
                        />
                    </div>
                </template>
            </InfiniteScroll>

            <p v-else class="py-20 text-center text-site-on-surface-variant">
                {{ t('app.site.gallery.empty') }}
            </p>
        </div>

        <!-- Lightbox -->
        <div
            v-if="lightboxImage"
            class="fixed inset-0 z-50 flex items-center justify-center bg-site-inverse-surface/90 p-4"
            role="dialog"
            aria-modal="true"
            @click.self="lightboxImage = null"
        >
            <button
                type="button"
                class="absolute top-5 right-5 text-site-inverse-on-surface"
                :aria-label="t('app.site.gallery.close')"
                @click="lightboxImage = null"
            >
                <svg
                    class="h-8 w-8"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1.5"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>
            <figure class="max-h-full max-w-4xl">
                <img
                    :src="lightboxImage.url"
                    :alt="
                        lightboxImage.productName ?? t('app.site.gallery.title')
                    "
                    class="max-h-[80vh] w-auto rounded-site-card object-contain"
                />
                <figcaption
                    v-if="lightboxImage.productName"
                    class="mt-4 flex items-center justify-between gap-4 text-site-inverse-on-surface"
                >
                    <span class="font-display text-lg">{{
                        lightboxImage.productName
                    }}</span>
                    <Link
                        v-if="lightboxImage.productSlug"
                        :href="productShow(lightboxImage.productSlug)"
                        class="text-sm underline underline-offset-4"
                    >
                        {{ t('app.site.gallery.view_product') }}
                    </Link>
                </figcaption>
            </figure>
        </div>
    </SiteLayout>
</template>
