<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import ThemeSwitcher from '@/components/site/ThemeSwitcher.vue';
import { useCompany } from '@/composables/useCompany';
import { useT } from '@/composables/useT';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();

const { t } = useT();
const { company } = useCompany();
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center gap-6 bg-site-surface p-6 font-site text-site-on-surface md:p-10"
    >
        <div class="w-full max-w-sm">
            <div
                class="rounded-site-card border border-site-outline-variant bg-site-surface-container-lowest p-6 shadow-site sm:p-8"
            >
                <div class="flex flex-col gap-8">
                    <div class="flex flex-col items-center gap-4">
                        <Link
                            :href="home()"
                            class="flex flex-col items-center gap-2"
                        >
                            <img
                                src="/images/logo.png"
                                :alt="company.name"
                                class="h-12 w-auto"
                            />
                            <span class="sr-only">{{ company.name }}</span>
                        </Link>
                        <div class="space-y-2 text-center">
                            <h1
                                class="font-display text-2xl text-site-on-surface"
                            >
                                {{ title ? t(title) : '' }}
                            </h1>
                            <p class="text-sm text-site-on-surface-variant">
                                {{ description ? t(description) : '' }}
                            </p>
                        </div>
                    </div>
                    <slot />
                </div>
            </div>

            <div class="mt-6 flex items-center justify-center gap-4">
                <Link
                    :href="home()"
                    class="inline-flex items-center gap-1.5 text-sm text-site-on-surface-variant transition-colors hover:text-site-primary"
                >
                    <ArrowLeft class="size-4" />
                    {{ t('app.auth.layout.back_to_site') }}
                </Link>
                <ThemeSwitcher />
            </div>
        </div>
    </div>
</template>
