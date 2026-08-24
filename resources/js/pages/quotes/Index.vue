<script setup lang="ts">
import { Head, Link, setLayoutProps } from '@inertiajs/vue3';
import AdminPagination from '@/components/admin/AdminPagination.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useT } from '@/composables/useT';
import AppLayout from '@/layouts/AppLayout.vue';
import { index as productsIndex } from '@/routes/products';
import { index as quotesIndex, show as quoteShow } from '@/routes/quotes';
import type { Paginated } from '@/types/admin';

type QuoteRow = {
    id: string;
    status: string;
    statusLabel: string;
    total: string;
    itemsCount: number;
    createdAt: string | null;
};

defineProps<{
    quotes: Paginated<QuoteRow>;
}>();

defineOptions({ layout: AppLayout });

const { t } = useT();

setLayoutProps({
    breadcrumbs: [
        {
            title: 'app.nav.breadcrumbs.quotes',
            href: quotesIndex(),
        },
    ],
});

function formatDate(value: string | null): string {
    return value ? new Date(value).toLocaleDateString('pt-BR') : '';
}
</script>

<template>
    <Head :title="t('app.quotes.title')" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <h1 class="text-2xl font-semibold">{{ t('app.quotes.title') }}</h1>

        <div
            v-if="quotes.data.length === 0"
            class="flex flex-col items-start gap-3 rounded-xl border border-dashed p-8"
        >
            <p class="text-muted-foreground">{{ t('app.quotes.empty') }}</p>
            <p class="text-sm text-muted-foreground">
                {{ t('app.quotes.empty_hint') }}
            </p>
            <Button as-child>
                <Link :href="productsIndex()">{{
                    t('app.quotes.browse_products')
                }}</Link>
            </Button>
        </div>

        <div v-else class="overflow-x-auto rounded-xl border">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b text-left">
                        <th class="px-4 py-3 font-medium">
                            {{ t('app.quotes.created_at') }}
                        </th>
                        <th class="px-4 py-3 font-medium">
                            {{ t('app.quotes.status') }}
                        </th>
                        <th class="px-4 py-3 font-medium">
                            {{ t('app.quotes.items_count') }}
                        </th>
                        <th class="px-4 py-3 font-medium">
                            {{ t('app.quotes.total') }}
                        </th>
                        <th class="px-4 py-3" />
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="quote in quotes.data"
                        :key="quote.id"
                        class="border-b last:border-b-0"
                    >
                        <td class="px-4 py-3">
                            {{ formatDate(quote.createdAt) }}
                        </td>
                        <td class="px-4 py-3">
                            <Badge variant="secondary">{{
                                quote.statusLabel
                            }}</Badge>
                        </td>
                        <td class="px-4 py-3">{{ quote.itemsCount }}</td>
                        <td class="px-4 py-3">R$ {{ quote.total }}</td>
                        <td class="px-4 py-3 text-right">
                            <Button variant="outline" size="sm" as-child>
                                <Link :href="quoteShow(quote.id)">{{
                                    t('app.quotes.view')
                                }}</Link>
                            </Button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <AdminPagination :links="quotes.links" />
    </div>
</template>
