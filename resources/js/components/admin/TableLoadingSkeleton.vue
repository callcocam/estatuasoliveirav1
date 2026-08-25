<script setup lang="ts">
import { Skeleton } from '@/components/ui/skeleton';

withDefaults(
    defineProps<{
        columns: number;
        rows?: number;
    }>(),
    { rows: 5 },
);
</script>

<template>
    <tr
        v-for="row in rows"
        :key="row"
        class="border-b border-border last:border-b-0"
    >
        <td v-for="column in columns" :key="column" class="px-4 py-3">
            <Skeleton class="h-5 w-full max-w-40" />
        </td>
    </tr>
</template>
