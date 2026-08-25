<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { RotateCcw } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { useT } from '@/composables/useT';

const props = defineProps<{
    href: string;
}>();

const { t } = useT();

const restoring = ref(false);

function restore(): void {
    router.post(
        props.href,
        {},
        {
            preserveScroll: true,
            onStart: () => (restoring.value = true),
            onFinish: () => (restoring.value = false),
        },
    );
}
</script>

<template>
    <Button
        size="icon"
        variant="outline"
        type="button"
        :disabled="restoring"
        :aria-label="t('app.admin.common.restore')"
        @click="restore"
    >
        <RotateCcw class="size-4" />
    </Button>
</template>
