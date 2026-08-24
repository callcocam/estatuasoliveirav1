<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
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
        class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10"
    >
        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-4">
                    <Link
                        :href="home()"
                        class="flex flex-col items-center gap-2 font-medium"
                    >
                        <img
                            :src="company.logoUrl"
                            :alt="company.name"
                            class="mb-1 h-9 w-auto"
                        />
                        <span class="sr-only">{{ title ? t(title) : '' }}</span>
                    </Link>
                    <div class="space-y-2 text-center">
                        <h1 class="text-xl font-medium">
                            {{ title ? t(title) : '' }}
                        </h1>
                        <p class="text-center text-sm text-muted-foreground">
                            {{ description ? t(description) : '' }}
                        </p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
