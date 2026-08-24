<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Copy, Pencil, Plus, RotateCcw, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import AdminPagination from '@/components/admin/AdminPagination.vue';
import ConfirmDeleteDialog from '@/components/admin/ConfirmDeleteDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
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
    create as productCreate,
    destroy as productDestroy,
    duplicate as productDuplicate,
    edit as productEdit,
    index as productsIndex,
    restore as productRestore,
} from '@/routes/admin/products';
import type { Paginated } from '@/types/admin';

defineOptions({ layout: AdminLayout });

type ProductRow = {
    id: string;
    name: string;
    slug: string;
    reference: string | null;
    categoryName: string | null;
    status: string;
    statusLabel: string;
    featured: boolean;
    stock: number | null;
    image: string | null;
    deleted: boolean;
};

const props = defineProps<{
    products: Paginated<ProductRow>;
    categories: { id: string; name: string }[];
    filters: {
        status: string | null;
        category: string | null;
        search: string | null;
    };
}>();

const { t } = useT();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? 'all');
const category = ref(props.filters.category ?? 'all');
const deleting = ref<ProductRow | null>(null);

function applyFilters() {
    router.get(
        productsIndex().url,
        {
            search: search.value || undefined,
            status: status.value !== 'all' ? status.value : undefined,
            category: category.value !== 'all' ? category.value : undefined,
        },
        { preserveState: true, preserveScroll: true },
    );
}

function confirmDelete() {
    if (!deleting.value) {
        return;
    }

    router.delete(productDestroy(deleting.value.slug).url, {
        preserveScroll: true,
        onFinish: () => (deleting.value = null),
    });
}
</script>

<template>
    <Head :title="t('app.admin.products.title')" />

    <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold">
            {{ t('app.admin.products.title') }}
        </h1>
        <Button as-child>
            <Link :href="productCreate().url">
                <Plus />
                {{ t('app.admin.products.new') }}
            </Link>
        </Button>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <Input
            v-model="search"
            type="search"
            :placeholder="t('app.admin.common.search_placeholder')"
            class="max-w-xs"
            @keydown.enter="applyFilters"
        />
        <Select v-model="status" @update:model-value="applyFilters">
            <SelectTrigger class="w-44">
                <SelectValue />
            </SelectTrigger>
            <SelectContent>
                <SelectItem value="all">{{
                    t('app.admin.common.filter_all')
                }}</SelectItem>
                <SelectItem value="draft">{{
                    t('app.admin.status.draft')
                }}</SelectItem>
                <SelectItem value="published">{{
                    t('app.admin.status.published')
                }}</SelectItem>
                <SelectItem value="archived">{{
                    t('app.admin.status.archived')
                }}</SelectItem>
                <SelectItem value="trashed">{{
                    t('app.admin.products.filter_trashed')
                }}</SelectItem>
            </SelectContent>
        </Select>
        <Select v-model="category" @update:model-value="applyFilters">
            <SelectTrigger class="w-52">
                <SelectValue />
            </SelectTrigger>
            <SelectContent>
                <SelectItem value="all">{{
                    t('app.admin.common.filter_all')
                }}</SelectItem>
                <SelectItem
                    v-for="option in categories"
                    :key="option.id"
                    :value="option.id"
                >
                    {{ option.name }}
                </SelectItem>
            </SelectContent>
        </Select>
    </div>

    <div class="overflow-x-auto rounded-lg border">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b bg-muted/50 text-left">
                    <th class="px-4 py-3 font-medium">
                        {{ t('app.admin.common.name') }}
                    </th>
                    <th class="px-4 py-3 font-medium">
                        {{ t('app.admin.products.fields.category') }}
                    </th>
                    <th class="px-4 py-3 font-medium">
                        {{ t('app.admin.common.status') }}
                    </th>
                    <th class="px-4 py-3 font-medium">
                        {{ t('app.admin.products.fields.stock') }}
                    </th>
                    <th class="px-4 py-3 text-right font-medium">
                        {{ t('app.admin.common.actions') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="products.data.length === 0">
                    <td
                        colspan="5"
                        class="px-4 py-8 text-center text-muted-foreground"
                    >
                        {{ t('app.admin.common.empty') }}
                    </td>
                </tr>
                <tr
                    v-for="product in products.data"
                    :key="product.id"
                    class="border-b last:border-b-0"
                    :class="{ 'opacity-60': product.deleted }"
                >
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <img
                                v-if="product.image"
                                :src="product.image"
                                alt=""
                                class="size-10 rounded-md object-cover"
                            />
                            <div
                                v-else
                                class="size-10 rounded-md bg-muted"
                                aria-hidden="true"
                            ></div>
                            <div class="min-w-0">
                                <p class="truncate font-medium">
                                    {{ product.name }}
                                </p>
                                <p
                                    class="truncate text-xs text-muted-foreground"
                                >
                                    {{
                                        product.reference ??
                                        t('app.admin.common.none')
                                    }}
                                    <span
                                        v-if="product.featured"
                                        class="ml-1 text-primary"
                                    >
                                        · {{ t('app.admin.common.featured') }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        {{
                            product.categoryName ??
                            t('app.admin.products.no_category')
                        }}
                    </td>
                    <td class="px-4 py-3">
                        <Badge v-if="product.deleted" variant="destructive">
                            {{ t('app.admin.common.deleted_badge') }}
                        </Badge>
                        <Badge
                            v-else
                            :variant="
                                product.status === 'published'
                                    ? 'default'
                                    : 'secondary'
                            "
                        >
                            {{ product.statusLabel }}
                        </Badge>
                    </td>
                    <td class="px-4 py-3">
                        {{ product.stock ?? t('app.admin.common.none') }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-1">
                            <template v-if="!product.deleted">
                                <Button
                                    variant="outline"
                                    size="icon"
                                    as-child
                                    :aria-label="t('app.admin.common.edit')"
                                >
                                    <Link :href="productEdit(product.slug).url">
                                        <Pencil />
                                    </Link>
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="icon"
                                    :aria-label="
                                        t('app.admin.common.duplicate')
                                    "
                                    @click="
                                        router.post(
                                            productDuplicate(product.slug).url,
                                        )
                                    "
                                >
                                    <Copy />
                                </Button>
                                <Button
                                    type="button"
                                    variant="destructive"
                                    size="icon"
                                    :aria-label="t('app.admin.common.delete')"
                                    @click="deleting = product"
                                >
                                    <Trash2 />
                                </Button>
                            </template>
                            <Button
                                v-else
                                type="button"
                                variant="outline"
                                @click="
                                    router.post(
                                        productRestore(product.slug).url,
                                        {},
                                        { preserveScroll: true },
                                    )
                                "
                            >
                                <RotateCcw />
                                {{ t('app.admin.common.restore') }}
                            </Button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <AdminPagination :links="products.links" />

    <ConfirmDeleteDialog
        :open="deleting !== null"
        @update:open="deleting = $event ? deleting : null"
        @confirm="confirmDelete"
    />
</template>
