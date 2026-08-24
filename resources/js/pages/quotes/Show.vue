<script setup lang="ts">
import { Head, Link, setLayoutProps } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useT } from '@/composables/useT';
import AppLayout from '@/layouts/AppLayout.vue';
import { show as productShow } from '@/routes/products';
import { index as quotesIndex, show as quoteShow } from '@/routes/quotes';

type QuoteDetail = {
    id: string;
    status: string;
    statusLabel: string;
    total: string;
    notes: string | null;
    createdAt: string | null;
    items: {
        id: string;
        name: string;
        quantity: number;
        unitPrice: string;
        total: string;
        productSlug: string | null;
    }[];
};

const props = defineProps<{
    quote: QuoteDetail;
}>();

defineOptions({ layout: AppLayout });

const { t } = useT();

setLayoutProps({
    breadcrumbs: [
        {
            title: 'app.nav.breadcrumbs.quotes',
            href: quotesIndex(),
        },
        {
            title: 'app.nav.breadcrumbs.quote_detail',
            href: quoteShow(props.quote.id),
        },
    ],
});

function formatDate(value: string | null): string {
    return value ? new Date(value).toLocaleDateString('pt-BR') : '';
}
</script>

<template>
    <Head :title="t('app.quotes.detail')" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold">
                    {{ t('app.quotes.detail') }}
                </h1>
                <Badge>{{ quote.statusLabel }}</Badge>
            </div>
            <Button variant="outline" as-child>
                <Link :href="quotesIndex()">{{ t('app.quotes.back') }}</Link>
            </Button>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle>{{ t('app.quotes.created_at') }}</CardTitle>
                </CardHeader>
                <CardContent>{{ formatDate(quote.createdAt) }}</CardContent>
            </Card>
            <Card>
                <CardHeader>
                    <CardTitle>{{ t('app.quotes.total') }}</CardTitle>
                </CardHeader>
                <CardContent>R$ {{ quote.total }}</CardContent>
            </Card>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>{{ t('app.quotes.items') }}</CardTitle>
            </CardHeader>
            <CardContent>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b text-left">
                            <th class="px-2 py-2 font-medium">
                                {{ t('app.quotes.item_name') }}
                            </th>
                            <th class="px-2 py-2 font-medium">
                                {{ t('app.quotes.quantity') }}
                            </th>
                            <th class="px-2 py-2 font-medium">
                                {{ t('app.quotes.unit_price') }}
                            </th>
                            <th class="px-2 py-2 font-medium">
                                {{ t('app.quotes.total') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in quote.items"
                            :key="item.id"
                            class="border-b last:border-b-0"
                        >
                            <td class="px-2 py-2">
                                <Link
                                    v-if="item.productSlug"
                                    :href="productShow(item.productSlug)"
                                    class="underline underline-offset-4"
                                >
                                    {{ item.name }}
                                </Link>
                                <span v-else>{{ item.name }}</span>
                            </td>
                            <td class="px-2 py-2">{{ item.quantity }}</td>
                            <td class="px-2 py-2">R$ {{ item.unitPrice }}</td>
                            <td class="px-2 py-2">R$ {{ item.total }}</td>
                        </tr>
                    </tbody>
                </table>
            </CardContent>
        </Card>

        <Card v-if="quote.notes">
            <CardHeader>
                <CardTitle>{{ t('app.quotes.notes') }}</CardTitle>
            </CardHeader>
            <CardContent class="whitespace-pre-line">{{
                quote.notes
            }}</CardContent>
        </Card>
    </div>
</template>
