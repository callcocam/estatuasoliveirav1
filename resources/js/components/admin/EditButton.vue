<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Pencil } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { useT } from '@/composables/useT';

defineProps<{
    href: string;
}>();

const { t } = useT();
</script>

<template>
    <Button as-child size="icon" variant="outline">
        <Link :href="href" :aria-label="t('app.admin.common.edit')">
            <Pencil class="size-4" />
        </Link>
    </Button>
</template>
