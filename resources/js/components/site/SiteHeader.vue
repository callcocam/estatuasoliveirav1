<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import SiteAppearanceToggle from '@/components/site/SiteAppearanceToggle.vue';
import SiteUserMenu from '@/components/site/SiteUserMenu.vue';
import ThemeSwitcher from '@/components/site/ThemeSwitcher.vue';
import { useCompany } from '@/composables/useCompany';
import { useT } from '@/composables/useT';
import { about, contact, gallery, home } from '@/routes';
import { index as productsIndex } from '@/routes/products';

const { t } = useT();
const { company, whatsappUrl } = useCompany();
const page = usePage();

const mobileMenuOpen = ref(false);

watch(
    () => page.url,
    () => {
        mobileMenuOpen.value = false;
    },
);

const links = computed(() => [
    { label: t('app.site.nav.home'), href: home(), active: page.url === '/' },
    {
        label: t('app.site.nav.about'),
        href: about(),
        active: page.url.startsWith('/nossa-historia'),
    },
    {
        label: t('app.site.nav.products'),
        href: productsIndex(),
        active: page.url.startsWith('/produtos'),
    },
    {
        label: t('app.site.nav.gallery'),
        href: gallery(),
        active: page.url.startsWith('/galeria'),
    },
    {
        label: t('app.site.nav.contact'),
        href: contact(),
        active: page.url.startsWith('/contato'),
    },
]);

const phoneHref = computed(() =>
    company.value.phone
        ? `tel:${company.value.phone.replace(/[^\d+]/g, '')}`
        : null,
);
</script>

<template>
    <header
        class="shadow-site-shadow/5 sticky top-0 z-40 border-b border-site-outline-variant bg-site-surface/95 shadow-sm backdrop-blur"
    >
        <!-- Top contact bar -->
        <div
            class="hidden bg-site-primary-container text-site-on-primary-container md:block"
        >
            <div
                class="mx-auto flex h-9 max-w-6xl items-center justify-between gap-4 px-4 text-xs md:px-6"
            >
                <div class="flex items-center gap-5">
                    <a
                        v-if="phoneHref"
                        :href="phoneHref"
                        class="inline-flex items-center gap-1.5 opacity-90 transition-opacity hover:opacity-100"
                    >
                        <svg
                            class="h-3.5 w-3.5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"
                            />
                        </svg>
                        {{ company.phone }}
                    </a>
                    <a
                        v-if="company.email"
                        :href="`mailto:${company.email}`"
                        class="inline-flex items-center gap-1.5 opacity-90 transition-opacity hover:opacity-100"
                    >
                        <svg
                            class="h-3.5 w-3.5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"
                            />
                        </svg>
                        {{ company.email }}
                    </a>
                </div>
                <div class="flex min-w-0 items-center gap-4">
                    <span
                        v-if="company.address"
                        class="hidden truncate opacity-90 lg:block"
                        >{{ company.address }}</span
                    >
                    <SiteAppearanceToggle compact />
                    <!-- <ThemeSwitcher compact /> -->
                    <SiteUserMenu topbar />
                </div>
            </div>
        </div>

        <!-- Main row -->
        <div
            class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4 md:h-[4.5rem] md:px-6"
        >
            <Link :href="home()" class="flex min-w-0 items-center gap-3">
                <img
                    :src="company.logoUrl"
                    :alt="company.name"
                    class="h-10 w-auto shrink-0 md:h-11"
                />
                <span class="min-w-0">
                    <span
                        class="block truncate font-display text-lg leading-tight text-site-primary md:text-xl"
                        >{{ company.name }}</span
                    >
                    <span
                        class="hidden truncate text-xs text-site-on-surface-variant md:block"
                        >{{ t('app.site.nav.tagline') }}</span
                    >
                </span>
            </Link>

            <nav
                class="hidden items-center gap-1 md:flex"
                :aria-label="t('app.site.nav.home')"
            >
                <Link
                    v-for="link in links"
                    :key="link.label"
                    :href="link.href"
                    :class="[
                        'relative rounded-site px-3 py-2 text-sm font-medium transition-colors',
                        link.active
                            ? 'text-site-primary after:absolute after:inset-x-3 after:-bottom-0.5 after:h-0.5 after:rounded-full after:bg-site-primary'
                            : 'text-site-on-surface-variant hover:bg-site-surface-container hover:text-site-on-surface',
                    ]"
                >
                    {{ link.label }}
                </Link>
            </nav>

            <div class="flex items-center gap-2 md:gap-3">
                <a
                    v-if="whatsappUrl"
                    :href="whatsappUrl"
                    target="_blank"
                    rel="noopener"
                    class="hidden items-center gap-2 rounded-site bg-site-primary px-4 py-2 text-sm font-medium text-site-on-primary transition-opacity hover:opacity-90 md:inline-flex"
                >
                    <svg
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="currentColor"
                        aria-hidden="true"
                    >
                        <path
                            d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38a9.87 9.87 0 004.74 1.21c5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2zm0 18.03a8.1 8.1 0 01-4.13-1.13l-.3-.18-3.12.82.83-3.04-.2-.31a8.07 8.07 0 01-1.24-4.28c0-4.47 3.64-8.1 8.11-8.1 4.47 0 8.1 3.63 8.1 8.1s-3.63 8.12-8.05 8.12zm4.44-6.07c-.24-.12-1.44-.71-1.66-.79-.22-.08-.39-.12-.55.12-.16.24-.63.79-.77.95-.14.16-.28.18-.53.06-.24-.12-1.03-.38-1.96-1.21-.72-.64-1.21-1.44-1.35-1.68-.14-.24-.02-.37.11-.5.11-.11.24-.28.37-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.55-1.32-.75-1.81-.2-.48-.4-.41-.55-.42h-.47c-.16 0-.42.06-.65.3-.22.24-.85.83-.85 2.03 0 1.2.87 2.36 1 2.52.12.16 1.72 2.62 4.16 3.68.58.25 1.03.4 1.39.51.58.19 1.11.16 1.53.1.47-.07 1.44-.59 1.64-1.16.2-.57.2-1.05.14-1.16-.06-.1-.22-.16-.46-.28z"
                        />
                    </svg>
                    {{ t('app.site.nav.quote_button') }}
                </a>
                <button
                    type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-site text-site-on-surface md:hidden"
                    :aria-label="
                        mobileMenuOpen
                            ? t('app.site.nav.close_menu')
                            : t('app.site.nav.open_menu')
                    "
                    :aria-expanded="mobileMenuOpen"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                >
                    <svg
                        v-if="!mobileMenuOpen"
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"
                        />
                    </svg>
                    <svg
                        v-else
                        class="h-6 w-6"
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
            </div>
        </div>

        <!-- Mobile menu -->
        <nav
            v-if="mobileMenuOpen"
            class="border-t border-site-outline-variant bg-site-surface px-4 pt-2 pb-4 md:hidden"
            :aria-label="t('app.site.nav.open_menu')"
        >
            <Link
                v-for="link in links"
                :key="link.label"
                :href="link.href"
                :class="[
                    'block rounded-site px-3 py-3 text-base',
                    link.active
                        ? 'bg-site-secondary-container font-medium text-site-on-secondary-container'
                        : 'text-site-on-surface-variant hover:bg-site-surface-container',
                ]"
            >
                {{ link.label }}
            </Link>
            <div class="mt-2 border-t border-site-outline-variant pt-2">
                <SiteUserMenu mobile />
            </div>
            <div class="mt-3 flex items-center justify-center gap-3">
                <SiteAppearanceToggle />
                <ThemeSwitcher />
            </div>
            <a
                v-if="whatsappUrl"
                :href="whatsappUrl"
                target="_blank"
                rel="noopener"
                class="mt-3 flex items-center justify-center gap-2 rounded-site bg-site-primary px-4 py-3 text-base font-medium text-site-on-primary"
            >
                {{ t('app.site.contact.whatsapp_button') }}
            </a>
        </nav>
    </header>
</template>
