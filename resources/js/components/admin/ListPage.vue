<script setup lang="ts">
import AdminPagination from '@/components/admin/AdminPagination.vue';
import TableLoadingSkeleton from '@/components/admin/TableLoadingSkeleton.vue';
import { useT } from '@/composables/useT';

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

withDefaults(
    defineProps<{
        title: string;
        description?: string;
        loading: boolean;
        empty: boolean;
        columns: number;
        links?: PaginationLink[];
    }>(),
    { links: () => [] },
);

const { t } = useT();
</script>

<template>
    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-foreground">
                    {{ title }}
                </h1>
                <p v-if="description" class="text-sm text-muted-foreground">
                    {{ description }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <slot name="actions" />
            </div>
        </div>

        <slot name="filters" />

        <div
            class="overflow-x-auto rounded-lg border border-border bg-background"
        >
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-muted/50 text-left">
                        <slot name="head" />
                    </tr>
                </thead>
                <tbody>
                    <TableLoadingSkeleton v-if="loading" :columns="columns" />
                    <tr v-else-if="empty">
                        <td
                            :colspan="columns"
                            class="px-4 py-8 text-center text-muted-foreground"
                        >
                            {{ t('app.admin.common.empty') }}
                        </td>
                    </tr>
                    <slot v-else name="body" />
                </tbody>
            </table>
        </div>

        <AdminPagination :links="links" />
    </div>
</template>
