<script setup lang="ts">
import { useVModel } from '@vueuse/core';
import type { HTMLAttributes } from 'vue';
import { useTemplateRef } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
    defaultValue?: string | number;
    modelValue?: string | number;
    class?: HTMLAttributes['class'];
}>();

const emits = defineEmits<{
    (e: 'update:modelValue', payload: string | number): void;
}>();

const modelValue = useVModel(props, 'modelValue', emits, {
    passive: true,
    defaultValue: props.defaultValue,
});

const inputRef = useTemplateRef('inputRef');

defineExpose({
    $el: inputRef,
    focus: () => inputRef.value?.focus(),
});
</script>

<template>
    <input
        ref="inputRef"
        v-model="modelValue"
        data-slot="input"
        :class="
            cn(
                'h-10 w-full min-w-0 rounded-site border border-site-outline-variant bg-site-surface-container-lowest px-3 py-1 text-base text-site-on-surface transition-[color,box-shadow] outline-none selection:bg-site-primary selection:text-site-on-primary placeholder:text-site-on-surface-variant/70 read-only:bg-site-surface-container-low disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                'focus-visible:border-site-primary focus-visible:ring-[3px] focus-visible:ring-site-primary/25',
                'aria-invalid:border-site-error aria-invalid:ring-site-error/20',
                props.class,
            )
        "
    />
</template>
