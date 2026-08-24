<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDeleteDialog from '@/components/admin/ConfirmDeleteDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
    destroy as quoteDestroy,
    index as quotesIndex,
    status as quoteStatus,
} from '@/routes/admin/quotes';

defineOptions({ layout: AdminLayout });

type QuoteDetail = {
    id: string;
    status: string;
    statusLabel: string;
    total: string;
    notes: string | null;
    createdAt: string | null;
    user: { name: string; email: string; phone: string | null } | null;
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
    statuses: { value: string; label: string }[];
}>();

const { t } = useT();

const statusValue = ref(props.quote.status);
const confirmingDelete = ref(false);

function changeStatus(value: unknown) {
    if (typeof value !== 'string' || value === props.quote.status) {
        return;
    }

    router.patch(
        quoteStatus(props.quote.id).url,
        { status: value },
        { preserveScroll: true },
    );
}

function confirmDelete() {
    router.delete(quoteDestroy(props.quote.id).url);
}
</script>

<template>
    <Head :title="t('app.admin.quotes.detail')" />

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-semibold">
                {{ t('app.admin.quotes.detail') }}
            </h1>
            <Badge>{{ quote.statusLabel }}</Badge>
        </div>
        <div class="flex items-center gap-2">
            <Select v-model="statusValue" @update:model-value="changeStatus">
                <SelectTrigger
                    class="w-48"
                    :aria-label="t('app.admin.quotes.change_status')"
                >
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="option in statuses"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <Button
                variant="destructive"
                type="button"
                @click="confirmingDelete = true"
            >
                {{ t('app.admin.common.delete') }}
            </Button>
            <Button variant="outline" as-child>
                <Link :href="quotesIndex().url">{{
                    t('app.admin.common.back')
                }}</Link>
            </Button>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <Card class="lg:col-span-2">
            <CardHeader>
                <CardTitle>{{ t('app.admin.quotes.items') }}</CardTitle>
            </CardHeader>
            <CardContent>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b text-left">
                            <th class="py-2 font-medium">
                                {{ t('app.admin.quotes.item_name') }}
                            </th>
                            <th class="py-2 font-medium">
                                {{ t('app.admin.quotes.quantity') }}
                            </th>
                            <th class="py-2 font-medium">
                                {{ t('app.admin.quotes.unit_price') }}
                            </th>
                            <th class="py-2 text-right font-medium">
                                {{ t('app.admin.quotes.total') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in quote.items"
                            :key="item.id"
                            class="border-b last:border-b-0"
                        >
                            <td class="py-2">{{ item.name }}</td>
                            <td class="py-2">{{ item.quantity }}</td>
                            <td class="py-2">R$ {{ item.unitPrice }}</td>
                            <td class="py-2 text-right">R$ {{ item.total }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="py-2 font-medium">
                                {{ t('app.admin.quotes.total') }}
                            </td>
                            <td class="py-2 text-right font-semibold">
                                R$ {{ quote.total }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </CardContent>
        </Card>

        <div class="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>{{ t('app.admin.quotes.customer') }}</CardTitle>
                </CardHeader>
                <CardContent class="space-y-1 text-sm">
                    <template v-if="quote.user">
                        <p class="font-medium">{{ quote.user.name }}</p>
                        <p class="text-muted-foreground">
                            {{ quote.user.email }}
                        </p>
                        <p
                            v-if="quote.user.phone"
                            class="text-muted-foreground"
                        >
                            {{ quote.user.phone }}
                        </p>
                    </template>
                    <p v-else class="text-muted-foreground">
                        {{ t('app.admin.quotes.no_customer') }}
                    </p>
                </CardContent>
            </Card>

            <Card v-if="quote.notes">
                <CardHeader>
                    <CardTitle>{{ t('app.admin.quotes.notes') }}</CardTitle>
                </CardHeader>
                <CardContent class="text-sm whitespace-pre-line">{{
                    quote.notes
                }}</CardContent>
            </Card>
        </div>
    </div>

    <ConfirmDeleteDialog
        v-model:open="confirmingDelete"
        @confirm="confirmDelete"
    />
</template>
