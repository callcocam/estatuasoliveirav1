<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Copy } from '@lucide/vue';
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
    create as productCreate,
    destroy as productDestroy,
    duplicate as productDuplicate,
    edit as productEdit,
    index as productsIndex,
    restore as productRestore,
} from '@/routes/admin/products';
import type { Paginated, ProductRow, ResourceAbilities } from '@/types/admin';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    products?: Paginated<ProductRow>;
    categories: { id: string; name: string }[];
    filters: {
        search: string;
        status: string;
        category: string;
        trashed: string;
        per_page: string;
    };
    can: ResourceAbilities;
}>();

const { t } = useT();

const { isLoading, isEmpty, rows, links } = useDeferredPaginator<ProductRow>(
    () => props.products,
);

const statusOptions = [
    { value: 'all', label: t('app.admin.common.filter_all') },
    { value: 'draft', label: t('app.admin.status.draft') },
    { value: 'published', label: t('app.admin.status.published') },
    { value: 'archived', label: t('app.admin.status.archived') },
];

function duplicateProduct(product: ProductRow): void {
    router.post(
        productDuplicate(product.slug).url,
        {},
        { preserveScroll: true },
    );
}
</script>

<template>
    <Head :title="t('app.admin.products.title')" />

    <ListPage
        :title="t('app.admin.products.title')"
        :loading="isLoading"
        :empty="isEmpty"
        :columns="5"
        :links="links"
    >
        <template #actions>
            <Button v-if="can.create" as-child>
                <Link :href="productCreate().url">{{
                    t('app.admin.products.new')
                }}</Link>
            </Button>
        </template>

        <template #filters>
            <ListFiltersBar
                :index-url="productsIndex().url"
                :filters="{
                    search: filters.search,
                    status: filters.status || 'all',
                    category: filters.category || 'all',
                    trashed: filters.trashed,
                    per_page: filters.per_page,
                }"
                :search-placeholder="t('app.admin.common.search_placeholder')"
            >
                <template #default="{ values, set }">
                    <Select
                        :model-value="values.status"
                        @update:model-value="
                            set('status', String($event ?? 'all'))
                        "
                    >
                        <SelectTrigger
                            class="w-44"
                            :aria-label="t('app.admin.products.filter_status')"
                        >
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="option in statusOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Select
                        :model-value="values.category"
                        @update:model-value="
                            set('category', String($event ?? 'all'))
                        "
                    >
                        <SelectTrigger
                            class="w-52"
                            :aria-label="
                                t('app.admin.products.fields.category')
                            "
                        >
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{
                                t('app.admin.common.filter_all')
                            }}</SelectItem>
                            <SelectItem
                                v-for="category in categories"
                                :key="category.id"
                                :value="category.id"
                            >
                                {{ category.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </template>
            </ListFiltersBar>
        </template>

        <template #head>
            <th class="px-4 py-3 font-medium">
                {{ t('app.admin.common.name') }}
            </th>
            <th class="hidden px-4 py-3 font-medium md:table-cell">
                {{ t('app.admin.products.fields.category') }}
            </th>
            <th class="px-4 py-3 font-medium">
                {{ t('app.admin.common.status') }}
            </th>
            <th class="hidden px-4 py-3 font-medium md:table-cell">
                {{ t('app.admin.products.fields.stock') }}
            </th>
            <th class="px-4 py-3 text-right font-medium">
                {{ t('app.admin.common.actions') }}
            </th>
        </template>

        <template #body>
            <tr
                v-for="product in rows"
                :key="product.id"
                class="border-b border-border last:border-b-0"
                :class="{ 'opacity-60': product.deleted }"
            >
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <img
                            v-if="product.image"
                            :src="product.image"
                            :alt="product.name"
                            class="size-10 rounded-md bg-muted object-cover"
                        />
                        <div
                            v-else
                            class="size-10 rounded-md bg-muted"
                            aria-hidden="true"
                        />
                        <div class="min-w-0">
                            <p class="truncate font-medium text-foreground">
                                {{ product.name }}
                            </p>
                            <p class="truncate text-xs text-muted-foreground">
                                {{
                                    product.reference ??
                                    t('app.admin.common.none')
                                }}
                            </p>
                        </div>
                    </div>
                </td>
                <td class="hidden px-4 py-3 md:table-cell">
                    {{
                        product.categoryName ??
                        t('app.admin.products.no_category')
                    }}
                </td>
                <td class="px-4 py-3">
                    <div class="flex flex-wrap items-center gap-1">
                        <Badge
                            :variant="
                                product.status === 'published'
                                    ? 'default'
                                    : 'secondary'
                            "
                        >
                            {{ product.statusLabel }}
                        </Badge>
                        <Badge v-if="product.featured" variant="outline">
                            {{ t('app.admin.common.featured') }}
                        </Badge>
                        <Badge v-if="product.deleted" variant="destructive">
                            {{ t('app.admin.common.deleted_badge') }}
                        </Badge>
                    </div>
                </td>
                <td class="hidden px-4 py-3 md:table-cell">
                    {{ product.stock }}
                </td>
                <td class="px-4 py-3">
                    <ColumnActions
                        :trashed="product.deleted"
                        :edit-href="productEdit(product.slug).url"
                        :delete-href="productDestroy(product.slug).url"
                        :restore-href="productRestore(product.slug).url"
                        :can-update="can.update"
                        :can-delete="can.delete"
                    >
                        <Button
                            v-if="can.create"
                            size="icon"
                            variant="outline"
                            type="button"
                            :aria-label="t('app.admin.common.duplicate')"
                            @click="duplicateProduct(product)"
                        >
                            <Copy class="size-4" />
                        </Button>
                    </ColumnActions>
                </td>
            </tr>
        </template>
    </ListPage>
</template>
