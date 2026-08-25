<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import ColumnActions from '@/components/admin/ColumnActions.vue';
import ListFiltersBar from '@/components/admin/ListFiltersBar.vue';
import ListPage from '@/components/admin/ListPage.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useDeferredPaginator } from '@/composables/useDeferredPaginator';
import { useT } from '@/composables/useT';
import AdminLayout from '@/layouts/AdminLayout.vue';
import {
    create as quoteCreate,
    destroy as quoteDestroy,
    index as quotesIndex,
    restore as quoteRestore,
    show as quoteShow,
} from '@/routes/admin/quotes';
import type { Paginated, QuoteRow, ResourceAbilities } from '@/types/admin';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    quotes?: Paginated<QuoteRow>;
    statuses: { value: string; label: string }[];
    filters: {
        search: string;
        status: string;
        trashed: string;
        per_page: string;
    };
    can: ResourceAbilities;
}>();

const { t } = useT();

const { isLoading, isEmpty, rows, links } = useDeferredPaginator<QuoteRow>(
    () => props.quotes,
);

function formatDate(value: string | null): string {
    return value ? new Date(value).toLocaleDateString('pt-BR') : '';
}
</script>

<template>
    <Head :title="t('app.admin.quotes.title')" />

    <ListPage
        :title="t('app.admin.quotes.title')"
        :loading="isLoading"
        :empty="isEmpty"
        :columns="5"
        :links="links"
    >
        <template #actions>
            <Button v-if="can.create" as-child>
                <Link :href="quoteCreate().url">
                    <Plus class="size-4" />
                    {{ t('app.admin.quotes.new') }}
                </Link>
            </Button>
        </template>

        <template #filters>
            <ListFiltersBar
                :index-url="quotesIndex().url"
                :filters="{
                    search: filters.search,
                    status: filters.status || 'all',
                    trashed: filters.trashed,
                    per_page: filters.per_page,
                }"
                :search-placeholder="t('app.admin.quotes.search_placeholder')"
            >
                <template #default="{ values, set }">
                    <Select
                        :model-value="values.status"
                        @update:model-value="set('status', String($event ?? 'all'))"
                    >
                        <SelectTrigger class="w-44" :aria-label="t('app.admin.common.status')">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">
                                {{ t('app.admin.common.filter_all') }}
                            </SelectItem>
                            <SelectItem
                                v-for="option in statuses"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </template>
            </ListFiltersBar>
        </template>

        <template #head>
            <th class="px-4 py-3 font-medium">{{ t('app.admin.quotes.customer') }}</th>
            <th class="px-4 py-3 font-medium">{{ t('app.admin.common.status') }}</th>
            <th class="hidden px-4 py-3 font-medium md:table-cell">
                {{ t('app.admin.quotes.items') }}
            </th>
            <th class="px-4 py-3 font-medium">{{ t('app.admin.quotes.total') }}</th>
            <th class="px-4 py-3 text-right font-medium">
                {{ t('app.admin.common.actions') }}
            </th>
        </template>

        <template #body>
            <tr
                v-for="quote in rows"
                :key="quote.id"
                class="border-b border-border last:border-b-0"
                :class="{ 'opacity-60': quote.deleted }"
            >
                <td class="px-4 py-3">
                    <p class="font-medium text-foreground">
                        {{ quote.userName ?? t('app.admin.quotes.no_customer') }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                        {{ formatDate(quote.createdAt) }}
                    </p>
                </td>
                <td class="px-4 py-3">
                    <div class="flex flex-wrap items-center gap-1">
                        <Badge :variant="quote.status === 'pending' ? 'default' : 'secondary'">
                            {{ quote.statusLabel }}
                        </Badge>
                        <Badge v-if="quote.deleted" variant="destructive">
                            {{ t('app.admin.common.deleted_badge') }}
                        </Badge>
                    </div>
                </td>
                <td class="hidden px-4 py-3 md:table-cell">{{ quote.itemsCount }}</td>
                <td class="px-4 py-3">R$ {{ quote.total }}</td>
                <td class="px-4 py-3">
                    <ColumnActions
                        :trashed="quote.deleted"
                        :edit-href="quoteShow(quote.id).url"
                        :delete-href="quoteDestroy(quote.id).url"
                        :restore-href="quoteRestore(quote.id).url"
                        :can-update="can.update"
                        :can-delete="can.delete"
                    />
                </td>
            </tr>
        </template>
    </ListPage>
</template>
