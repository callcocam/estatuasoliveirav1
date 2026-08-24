<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useCompany } from '@/composables/useCompany';
import { useT } from '@/composables/useT';
import { about, contact, gallery, home, terms } from '@/routes';
import { index as productsIndex } from '@/routes/products';

const { t } = useT();
const { company, whatsappUrl } = useCompany();

const links = computed(() => [
    { label: t('app.site.nav.home'), href: home() },
    { label: t('app.site.nav.about'), href: about() },
    { label: t('app.site.nav.products'), href: productsIndex() },
    { label: t('app.site.nav.gallery'), href: gallery() },
    { label: t('app.site.nav.contact'), href: contact() },
    { label: t('app.site.nav.terms'), href: terms() },
]);

const year = new Date().getFullYear();
</script>

<template>
    <footer class="border-t border-site-outline-variant bg-site-surface-container-low">
        <div class="mx-auto grid max-w-6xl gap-10 px-4 py-14 md:grid-cols-3 md:px-6">
            <div>
                <p class="font-display text-xl text-site-primary">{{ company.name }}</p>
                <p class="mt-3 max-w-xs text-sm leading-relaxed text-site-on-surface-variant">
                    {{ t('app.site.footer.tagline') }}
                </p>
            </div>

            <nav :aria-label="t('app.site.footer.navigation')">
                <p class="text-xs font-semibold tracking-widest text-site-on-surface uppercase">
                    {{ t('app.site.footer.navigation') }}
                </p>
                <ul class="mt-4 space-y-2">
                    <li v-for="link in links" :key="link.label">
                        <Link :href="link.href" class="text-sm text-site-on-surface-variant transition-colors hover:text-site-primary">
                            {{ link.label }}
                        </Link>
                    </li>
                </ul>
            </nav>

            <div>
                <p class="text-xs font-semibold tracking-widest text-site-on-surface uppercase">
                    {{ t('app.site.footer.contact') }}
                </p>
                <ul class="mt-4 space-y-2 text-sm text-site-on-surface-variant">
                    <li v-if="company.phone">
                        <a :href="`tel:${company.phone.replace(/\D/g, '')}`" class="hover:text-site-primary">{{ company.phone }}</a>
                    </li>
                    <li v-if="whatsappUrl">
                        <a :href="whatsappUrl" target="_blank" rel="noopener" class="hover:text-site-primary">WhatsApp</a>
                    </li>
                    <li v-if="company.email">
                        <a :href="`mailto:${company.email}`" class="hover:text-site-primary">{{ company.email }}</a>
                    </li>
                    <li v-if="company.address">{{ company.address }}</li>
                </ul>
            </div>
        </div>

        <div class="border-t border-site-outline-variant py-6">
            <p class="mx-auto max-w-6xl px-4 text-xs text-site-on-surface-variant md:px-6">
                © {{ year }} {{ company.name }}. {{ t('app.site.footer.rights') }}
            </p>
        </div>
    </footer>
</template>
