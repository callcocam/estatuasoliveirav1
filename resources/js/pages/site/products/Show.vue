<script setup lang="ts">
import ProductCard from '@/components/site/ProductCard.vue';
import WhatsAppButton from '@/components/site/WhatsAppButton.vue';
import { useT } from '@/composables/useT';
import SiteLayout from '@/layouts/SiteLayout.vue';
import { contact } from '@/routes';
import { index as productsIndex } from '@/routes/products';
import type { SiteProductCard, SiteProductDetail } from '@/types/site';
import { Deferred, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    product: SiteProductDetail;
    relatedProducts?: SiteProductCard[];
}>();

const { t } = useT();

const activeImageIndex = ref(0);
const activeImage = computed(
    () => props.product.images[activeImageIndex.value] ?? null,
);

const whatsappQuoteMessage = computed(
    () =>
        `${props.product.name}${props.product.reference ? ` (Ref. ${props.product.reference})` : ''}`,
);
</script>

<template>
    <SiteLayout
        :title="product.name"
        :description="product.description"
        :og-image="activeImage?.url"
    >
        <div class="mx-auto max-w-6xl px-4 py-14 md:px-6 md:py-20">
            <Link
                :href="
                    product.category
                        ? productsIndex({
                              query: { categoria: product.category.slug },
                          })
                        : productsIndex()
                "
                class="text-sm text-site-on-surface-variant transition-colors hover:text-site-primary"
            >
                ← {{ t('app.site.product.back_to_catalog') }}
            </Link>

            <div class="mt-8 grid gap-10 lg:grid-cols-2">
                <!-- Galeria -->
                <div>
                    <div
                        class="aspect-square overflow-hidden rounded-site-card bg-site-surface-container"
                    >
                        <img
                            v-if="activeImage"
                            :src="activeImage.url"
                            :alt="product.name"
                            class="h-full w-full object-cover"
                        />
                    </div>
                    <div
                        v-if="product.images.length > 1"
                        class="mt-4 flex gap-3 overflow-x-auto pb-1"
                    >
                        <button
                            v-for="(image, index) in product.images"
                            :key="image.id"
                            type="button"
                            :aria-label="
                                t('app.site.product.gallery_image', {
                                    position: index + 1,
                                    total: product.images.length,
                                })
                            "
                            :class="[
                                'h-20 w-20 shrink-0 overflow-hidden rounded-site border-2 transition-colors',
                                index === activeImageIndex
                                    ? 'border-site-primary'
                                    : 'border-transparent',
                            ]"
                            @click="activeImageIndex = index"
                        >
                            <img
                                :src="image.url"
                                :alt="product.name"
                                loading="lazy"
                                class="h-full w-full object-cover"
                            />
                        </button>
                    </div>
                </div>

                <!-- Detalhes -->
                <div>
                    <p
                        v-if="product.category"
                        class="text-xs tracking-widest text-site-on-surface-variant uppercase"
                    >
                        {{ product.category.name }}
                    </p>
                    <h1
                        class="mt-2 font-display text-3xl text-site-on-surface md:text-4xl"
                    >
                        {{ product.name }}
                    </h1>

                    <p
                        v-if="product.description"
                        class="mt-6 leading-relaxed whitespace-pre-line text-site-on-surface-variant"
                    >
                        {{ product.description }}
                    </p>

                    <dl
                        class="mt-8 grid grid-cols-2 gap-x-6 gap-y-4 border-t border-site-outline-variant pt-8 text-sm"
                    >
                        <template v-if="product.reference">
                            <div>
                                <dt class="text-site-on-surface-variant">
                                    {{ t('app.site.product.reference') }}
                                </dt>
                                <dd
                                    class="mt-1 font-medium text-site-on-surface"
                                >
                                    {{ product.reference }}
                                </dd>
                            </div>
                        </template>
                        <template v-if="product.widthCm && product.heightCm">
                            <div>
                                <dt class="text-site-on-surface-variant">
                                    {{ t('app.site.product.dimensions') }}
                                </dt>
                                <dd
                                    class="mt-1 font-medium text-site-on-surface"
                                >
                                    {{
                                        t('app.site.product.dimensions_value', {
                                            width: product.widthCm,
                                            height: product.heightCm,
                                        })
                                    }}
                                </dd>
                            </div>
                        </template>
                        <template v-if="product.weightKg">
                            <div>
                                <dt class="text-site-on-surface-variant">
                                    {{ t('app.site.product.weight') }}
                                </dt>
                                <dd
                                    class="mt-1 font-medium text-site-on-surface"
                                >
                                    {{
                                        t('app.site.product.weight_value', {
                                            weight: product.weightKg,
                                        })
                                    }}
                                </dd>
                            </div>
                        </template>
                    </dl>

                    <!-- CTA orçamento -->
                    <div
                        class="mt-10 rounded-site-card bg-site-primary-container p-6"
                    >
                        <h2
                            class="font-display text-xl text-site-on-primary-container"
                        >
                            {{ t('app.site.product.quote_title') }}
                        </h2>
                        <p
                            class="mt-2 text-sm text-site-on-primary-container/90"
                        >
                            {{ t('app.site.product.quote_text') }}
                        </p>
                        <div class="mt-5 flex flex-wrap gap-3">
                            <Link
                                :href="contact()"
                                class="rounded-site bg-site-surface px-6 py-2.5 text-sm font-medium text-site-primary transition-colors hover:bg-site-surface-container"
                            >
                                {{ t('app.site.product.quote_button') }}
                            </Link>
                            <WhatsAppButton
                                :message="whatsappQuoteMessage"
                                :label="t('app.site.product.quote_whatsapp')"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Relacionados -->
            <section class="mt-20">
                <Deferred data="relatedProducts">
                    <template #fallback>
                        <div
                            class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4"
                        >
                            <div
                                v-for="index in 4"
                                :key="index"
                                class="aspect-[3/4] animate-pulse rounded-site-card bg-site-surface-container"
                            />
                        </div>
                    </template>

                    <template v-if="relatedProducts?.length">
                        <h2
                            class="mb-8 font-display text-2xl text-site-primary"
                        >
                            {{ t('app.site.product.related_title') }}
                        </h2>
                        <div
                            class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4"
                        >
                            <ProductCard
                                v-for="related in relatedProducts"
                                :key="related.id"
                                :product="related"
                            />
                        </div>
                    </template>
                </Deferred>
            </section>
        </div>
    </SiteLayout>
</template>
