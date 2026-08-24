<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import MediaUploader from '@/components/admin/MediaUploader.vue';
import type { MediaItem } from '@/components/admin/MediaUploader.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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
    index as productsIndex,
    store as productStore,
    update as productUpdate,
} from '@/routes/admin/products';

defineOptions({ layout: AdminLayout });

type ProductDetail = {
    id: string;
    categoryId: string | null;
    name: string;
    slug: string;
    reference: string | null;
    description: string | null;
    status: string;
    featured: boolean;
    price: string | null;
    widthCm: number | null;
    heightCm: number | null;
    weightKg: string | null;
    stock: number | null;
    sortOrder: number;
    media: MediaItem[];
};

const props = defineProps<{
    product: ProductDetail | null;
    categories: { id: string; name: string }[];
}>();

const { t } = useT();

const statusValue = ref(props.product?.status ?? 'draft');
const categoryValue = ref(props.product?.categoryId ?? 'none');
const featured = ref(props.product?.featured ?? false);
</script>

<template>
    <Head
        :title="
            product ? t('app.admin.products.edit') : t('app.admin.products.new')
        "
    />

    <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold">
            {{
                product
                    ? t('app.admin.products.edit')
                    : t('app.admin.products.new')
            }}
        </h1>
        <Button variant="outline" as-child>
            <Link :href="productsIndex().url">{{
                t('app.admin.common.back')
            }}</Link>
        </Button>
    </div>

    <Form
        v-bind="
            product ? productUpdate.form(product.slug) : productStore.form()
        "
        class="grid gap-6 lg:grid-cols-3"
        :transform="
            (data) => ({
                ...data,
                category_id: categoryValue === 'none' ? null : categoryValue,
                featured: featured,
            })
        "
        #default="{ errors, processing }"
    >
        <div class="space-y-6 lg:col-span-2">
            <Card>
                <CardContent class="space-y-4 pt-6">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="product-name">{{
                                t('app.admin.products.fields.name')
                            }}</Label>
                            <Input
                                id="product-name"
                                name="name"
                                :default-value="product?.name ?? ''"
                                required
                            />
                            <InputError :message="errors.name" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="product-slug">{{
                                t('app.admin.products.fields.slug')
                            }}</Label>
                            <Input
                                id="product-slug"
                                name="slug"
                                :default-value="product?.slug ?? ''"
                            />
                            <InputError :message="errors.slug" />
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label>{{
                                t('app.admin.products.fields.category')
                            }}</Label>
                            <Select v-model="categoryValue">
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="none">{{
                                        t('app.admin.products.no_category')
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
                            <InputError :message="errors.category_id" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="product-reference">{{
                                t('app.admin.products.fields.reference')
                            }}</Label>
                            <Input
                                id="product-reference"
                                name="reference"
                                :default-value="product?.reference ?? ''"
                            />
                            <InputError :message="errors.reference" />
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <Label for="product-description">{{
                            t('app.admin.products.fields.description')
                        }}</Label>
                        <textarea
                            id="product-description"
                            name="description"
                            rows="6"
                            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                            :value="product?.description ?? ''"
                        ></textarea>
                        <InputError :message="errors.description" />
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="grid gap-2">
                            <Label for="product-width">{{
                                t('app.admin.products.fields.width_cm')
                            }}</Label>
                            <Input
                                id="product-width"
                                name="width_cm"
                                type="number"
                                min="0"
                                :default-value="product?.widthCm ?? ''"
                            />
                            <InputError :message="errors.width_cm" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="product-height">{{
                                t('app.admin.products.fields.height_cm')
                            }}</Label>
                            <Input
                                id="product-height"
                                name="height_cm"
                                type="number"
                                min="0"
                                :default-value="product?.heightCm ?? ''"
                            />
                            <InputError :message="errors.height_cm" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="product-weight">{{
                                t('app.admin.products.fields.weight_kg')
                            }}</Label>
                            <Input
                                id="product-weight"
                                name="weight_kg"
                                type="number"
                                min="0"
                                step="0.01"
                                :default-value="product?.weightKg ?? ''"
                            />
                            <InputError :message="errors.weight_kg" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>{{ t('app.admin.products.images') }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <template v-if="product">
                        <p class="mb-4 text-sm text-muted-foreground">
                            {{ t('app.admin.products.images_hint') }}
                        </p>
                        <MediaUploader
                            mediable-type="product"
                            :mediable-id="product.id"
                            :media="product.media"
                        />
                    </template>
                    <p v-else class="text-sm text-muted-foreground">
                        {{ t('app.admin.products.save_before_images') }}
                    </p>
                </CardContent>
            </Card>
        </div>

        <div class="space-y-6">
            <Card>
                <CardContent class="space-y-4 pt-6">
                    <div class="grid gap-2">
                        <Label>{{
                            t('app.admin.products.fields.status')
                        }}</Label>
                        <Select v-model="statusValue" name="status">
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="draft">{{
                                    t('app.admin.status.draft')
                                }}</SelectItem>
                                <SelectItem value="published">{{
                                    t('app.admin.status.published')
                                }}</SelectItem>
                                <SelectItem value="archived">{{
                                    t('app.admin.status.archived')
                                }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.status" />
                    </div>
                    <label class="flex items-center gap-2 text-sm">
                        <Checkbox v-model="featured" />
                        {{ t('app.admin.products.fields.featured') }}
                    </label>
                    <InputError :message="errors.featured" />
                    <div class="grid gap-2">
                        <Label for="product-price">{{
                            t('app.admin.products.fields.price')
                        }}</Label>
                        <Input
                            id="product-price"
                            name="price"
                            type="number"
                            min="0"
                            step="0.01"
                            :default-value="product?.price ?? ''"
                        />
                        <InputError :message="errors.price" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="product-stock">{{
                            t('app.admin.products.fields.stock')
                        }}</Label>
                        <Input
                            id="product-stock"
                            name="stock"
                            type="number"
                            min="0"
                            :default-value="product?.stock ?? ''"
                        />
                        <InputError :message="errors.stock" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="product-sort">{{
                            t('app.admin.products.fields.sort_order')
                        }}</Label>
                        <Input
                            id="product-sort"
                            name="sort_order"
                            type="number"
                            min="0"
                            :default-value="product?.sortOrder ?? 0"
                        />
                        <InputError :message="errors.sort_order" />
                    </div>
                    <Button type="submit" class="w-full" :disabled="processing">
                        {{
                            processing
                                ? t('app.admin.common.saving')
                                : t('app.admin.common.save')
                        }}
                    </Button>
                </CardContent>
            </Card>
        </div>
    </Form>
</template>
