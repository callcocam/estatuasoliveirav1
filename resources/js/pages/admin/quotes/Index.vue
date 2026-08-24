<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref } from 'vue';
import AdminPagination from '@/components/admin/AdminPagination.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useT } from '@/composables/useT';
import AdminLayout from '@/layouts/AdminLayout.vue';
import {
    create as quoteCreate,
    index as quotesIndex,
    show as quoteShow,
} from '@/routes/admin/quotes';
import type { Paginated } from '@/types/admin';

defineOptions({ layout: AdminLayout });

type QuoteRow = {
    id: string;
    userName: string | null;
    status: string;
    statusLabel: string;
    total: string;
    itemsCount: number;
    createdAt: string | null;
};

const props = defineProps<{
    quotes: Paginated<QuoteRow>;
    statuses: { value: string; label: string }[];
    filters: { status: string | null };
}>();

const { t } = useT();

const status = ref(props.filters.status ?? 'all');

function applyFilters() {
    router.get(
        quotesIndex().url,
        { status: status.value !== 'all' ? status.value : undefined },
        { preserveState: true, preserveScroll: true },
    );
}

function formatDate(value: string | null): string {
    return value ? new Date(value).toLocaleDateString('pt-BR') : '';
}
</script>

<template>
    <Head :title="t('app.admin.quotes.title')" />

    <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold">
            {{ t('app.admin.quotes.title') }}
        </h1>
        <Button as-child>
            <Link :href="quoteCreate().url">
                <Plus />
                {{ t('app.admin.quotes.new') }}
            </Link>
        </Button>
    </div>

    <Select v-model="status" @update:model-value="applyFilters">
        <SelectTrigger class="w-52">
            <SelectValue />
        </SelectTrigger>
        <SelectContent>
            <SelectItem value="all">{{
                t('app.admin.common.filter_all')
            }}</SelectItem>
            <SelectItem
                v-for="option in statuses"
                :key="option.value"
                :value="option.value"
            >
                {{ option.label }}
            </SelectItem>
        </SelectContent>
    </Select>

    <div class="overflow-x-auto rounded-lg border">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b bg-muted/50 text-left">
                    <th class="px-4 py-3 font-medium">
                        {{ t('app.admin.quotes.customer') }}
                    </th>
                    <th class="px-4 py-3 font-medium">
                        {{ t('app.admin.common.status') }}
                    </th>
                    <th class="px-4 py-3 font-medium">
                        {{ t('app.admin.quotes.items') }}
                    </th>
                    <th class="px-4 py-3 font-medium">
                        {{ t('app.admin.quotes.total') }}
                    </th>
                    <th class="px-4 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="quotes.data.length === 0">
                    <td
                        colspan="5"
                        class="px-4 py-8 text-center text-muted-foreground"
                    >
                        {{ t('app.admin.common.empty') }}
                    </td>
                </tr>
                <tr
                    v-for="quote in quotes.data"
                    :key="quote.id"
                    class="border-b last:border-b-0"
                >
                    <td class="px-4 py-3">
                        <p class="font-medium">
                            {{
                                quote.userName ??
                                t('app.admin.quotes.no_customer')
                            }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ formatDate(quote.createdAt) }}
                        </p>
                    </td>
                    <td class="px-4 py-3">
                        <Badge
                            :variant="
                                quote.status === 'pending'
                                    ? 'default'
                                    : 'secondary'
                            "
                        >
                            {{ quote.statusLabel }}
                        </Badge>
                    </td>
                    <td class="px-4 py-3">{{ quote.itemsCount }}</td>
                    <td class="px-4 py-3">R$ {{ quote.total }}</td>
                    <td class="px-4 py-3 text-right">
                        <Button variant="outline" size="sm" as-child>
                            <Link :href="quoteShow(quote.id).url">{{
                                t('app.admin.common.edit')
                            }}</Link>
                        </Button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <AdminPagination :links="quotes.links" />
</template>
