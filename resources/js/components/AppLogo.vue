<script setup lang="ts">
import { useCompany } from '@/composables/useCompany';

const { company } = useCompany();
</script>

<template>
    <img
        src="/images/logo.png"
        :alt="company.name"
        class="aspect-square size-8 shrink-0 rounded-md object-contain"
    />
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span
            class="mb-0.5 truncate font-display leading-tight font-semibold"
            >{{ company.name }}</span
        >
    </div>
</template>
