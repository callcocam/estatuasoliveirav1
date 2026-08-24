<script setup lang="ts">
defineProps<{
    title: string;
    subtitle?: string | null;
    align?: 'left' | 'center';
}>();
</script>

<template>
    <div
        :class="[
            'mb-10 max-w-2xl',
            align === 'center' ? 'mx-auto text-center' : '',
        ]"
    >
        <h2 class="font-display text-3xl text-site-primary md:text-4xl">
            {{ title }}
        </h2>
        <p v-if="subtitle" class="mt-3 text-base text-site-on-surface-variant">
            {{ subtitle }}
        </p>
    </div>
</template>
