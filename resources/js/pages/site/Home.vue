<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import ProductCard from '@/components/site/ProductCard.vue';
import SectionHeading from '@/components/site/SectionHeading.vue';
import WhatsAppButton from '@/components/site/WhatsAppButton.vue';
import { useT } from '@/composables/useT';
import SiteLayout from '@/layouts/SiteLayout.vue';
import { about, contact } from '@/routes';
import { index as productsIndex } from '@/routes/products';
import type {
    SiteCategorySummary,
    SiteProductCard,
    SiteSlider,
} from '@/types/site';

const props = defineProps<{
    sliders: SiteSlider[];
    featuredProducts: SiteProductCard[];
    categories: SiteCategorySummary[];
}>();

const { t } = useT();

const currentSlide = ref(0);
let slideTimer: ReturnType<typeof setInterval> | undefined;

const activeSlider = computed(() => props.sliders[currentSlide.value] ?? null);

onMounted(() => {
    if (props.sliders.length > 1) {
        slideTimer = setInterval(() => {
            currentSlide.value =
                (currentSlide.value + 1) % props.sliders.length;
        }, 6000);
    }
});

onBeforeUnmount(() => {
    if (slideTimer) {
        clearInterval(slideTimer);
    }
});
</script>

<template>
    <SiteLayout
        :title="t('app.site.meta.home_title')"
        :description="t('app.site.meta.home_description')"
        :og-image="sliders[0]?.image"
    >
        <!-- Hero -->
        <section
            class="relative flex min-h-[70vh] items-center justify-center overflow-hidden md:min-h-[85vh]"
        >
            <template v-for="(slider, index) in sliders" :key="slider.id">
                <div
                    v-if="slider.image"
                    :class="[
                        'absolute inset-0 transition-opacity duration-1000',
                        index === currentSlide ? 'opacity-100' : 'opacity-0',
                    ]"
                >
                    <img
                        :src="slider.image"
                        alt=""
                        aria-hidden="true"
                        class="absolute inset-0 h-full w-full scale-110 object-cover blur-xl"
                    />
                    <img
                        :src="slider.image"
                        :alt="slider.title"
                        class="absolute inset-0 h-full w-full object-contain object-center"
                    />
                </div>
            </template>
            <div
                class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/55 to-black/40"
            />

            <div class="relative z-10 mx-auto max-w-3xl px-4 py-24 text-center">
                <h1
                    class="font-display text-4xl leading-tight text-white [text-shadow:0_2px_12px_rgb(0_0_0_/_0.6)] md:text-6xl"
                >
                    {{ activeSlider?.title ?? t('app.site.meta.home_title') }}
                </h1>
                <p
                    v-if="activeSlider?.subtitle || activeSlider?.description"
                    class="mt-5 text-lg text-white/90 [text-shadow:0_1px_8px_rgb(0_0_0_/_0.6)]"
                >
                    {{ activeSlider?.subtitle ?? activeSlider?.description }}
                </p>
                <div
                    class="mt-9 flex flex-wrap items-center justify-center gap-4"
                >
                    <Link
                        :href="productsIndex()"
                        class="rounded-site bg-site-primary px-7 py-3 text-sm font-medium text-site-on-primary shadow-md transition-transform hover:scale-[1.02]"
                    >
                        {{ t('app.site.home.hero_cta') }}
                    </Link>
                    <Link
                        :href="contact()"
                        class="rounded-site border border-white/70 px-7 py-3 text-sm font-medium text-white backdrop-blur-sm transition-colors hover:bg-white/10"
                    >
                        {{ t('app.site.home.hero_secondary_cta') }}
                    </Link>
                </div>
            </div>

            <div
                v-if="sliders.length > 1"
                class="absolute bottom-6 left-1/2 z-10 flex -translate-x-1/2 gap-2"
            >
                <button
                    v-for="(slider, index) in sliders"
                    :key="slider.id"
                    type="button"
                    :aria-label="slider.title"
                    :class="[
                        'h-2 rounded-full transition-all',
                        index === currentSlide
                            ? 'w-8 bg-white'
                            : 'w-2 bg-white/50',
                    ]"
                    @click="currentSlide = index"
                />
            </div>
        </section>

        <!-- Institucional -->
        <section class="mx-auto max-w-4xl px-4 py-20 text-center md:py-28">
            <p
                class="text-xs font-semibold tracking-[0.2em] text-site-tertiary uppercase"
            >
                {{ t('app.site.home.legacy_title') }}
            </p>
            <p
                class="mt-6 font-display text-2xl leading-relaxed text-site-on-surface md:text-3xl"
            >
                {{ t('app.site.home.legacy_text') }}
            </p>
            <Link
                :href="about()"
                class="mt-8 inline-block text-sm font-medium text-site-primary underline underline-offset-8"
            >
                {{ t('app.site.home.legacy_link') }}
            </Link>
        </section>

        <!-- Destaques -->
        <section
            v-if="featuredProducts.length"
            class="bg-site-surface-container-low py-20 md:py-24"
        >
            <div class="mx-auto max-w-6xl px-4 md:px-6">
                <SectionHeading
                    :title="t('app.site.home.featured_title')"
                    :subtitle="t('app.site.home.featured_subtitle')"
                />
                <div
                    class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4"
                >
                    <ProductCard
                        v-for="product in featuredProducts"
                        :key="product.id"
                        :product="product"
                    />
                </div>
                <div class="mt-10 text-center">
                    <Link
                        :href="productsIndex()"
                        class="text-sm font-medium text-site-primary underline underline-offset-8"
                    >
                        {{ t('app.site.home.featured_link') }}
                    </Link>
                </div>
            </div>
        </section>

        <!-- Categorias -->
        <section
            v-if="categories.length"
            class="mx-auto max-w-6xl px-4 py-20 md:px-6 md:py-24"
        >
            <SectionHeading
                :title="t('app.site.home.categories_title')"
                :subtitle="t('app.site.home.categories_subtitle')"
            />
            <div class="flex flex-wrap gap-3">
                <Link
                    v-for="category in categories"
                    :key="category.slug"
                    :href="
                        productsIndex({ query: { categoria: category.slug } })
                    "
                    class="rounded-site-badge border border-site-outline-variant bg-site-surface-container-lowest px-5 py-3 text-sm text-site-on-surface transition-colors hover:border-site-primary hover:text-site-primary"
                >
                    {{ category.name }}
                    <span
                        v-if="category.productsCount"
                        class="ml-2 text-xs text-site-on-surface-variant"
                    >
                        {{
                            t('app.site.home.categories_count', {
                                count: category.productsCount,
                            })
                        }}
                    </span>
                </Link>
            </div>
        </section>

        <!-- CTA -->
        <section class="bg-site-tertiary-container py-20 md:py-24">
            <div class="mx-auto max-w-3xl px-4 text-center">
                <h2
                    class="font-display text-3xl text-site-on-tertiary-container md:text-4xl"
                >
                    {{ t('app.site.home.cta_title') }}
                </h2>
                <p class="mt-4 text-base text-site-on-tertiary-container/90">
                    {{ t('app.site.home.cta_text') }}
                </p>
                <div
                    class="mt-8 flex flex-wrap items-center justify-center gap-4"
                >
                    <Link
                        :href="contact()"
                        class="rounded-site bg-site-surface px-7 py-3 text-sm font-medium text-site-primary shadow-md transition-colors hover:bg-site-surface-container"
                    >
                        {{ t('app.site.home.cta_button') }}
                    </Link>
                    <WhatsAppButton
                        :label="t('app.site.home.cta_whatsapp')"
                        class="px-7 py-3"
                    />
                </div>
            </div>
        </section>
    </SiteLayout>
</template>
