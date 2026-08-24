<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { computed } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
    variant?: 'primary' | 'secondary' | 'outline';
    class?: HTMLAttributes['class'];
}>();

const variantClasses = computed(() => {
    switch (props.variant ?? 'primary') {
        case 'secondary':
            return 'bg-site-secondary-container text-site-on-secondary-container hover:bg-site-secondary-container/80';
        case 'outline':
            return 'border border-site-outline-variant bg-transparent text-site-on-surface hover:bg-site-surface-container';
        default:
            return 'bg-site-primary text-site-on-primary hover:bg-site-primary/90';
    }
});
</script>

<template>
    <button
        data-slot="button"
        :class="
            cn(
                'inline-flex h-10 items-center justify-center gap-2 rounded-site px-4 text-sm font-semibold whitespace-nowrap transition-colors outline-none focus-visible:ring-[3px] focus-visible:ring-site-primary/40 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg:not([class*=\'size-\'])]:size-4',
                variantClasses,
                props.class,
            )
        "
    >
        <slot />
    </button>
</template>
