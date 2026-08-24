<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Plus, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
    create as quoteCreate,
    index as quotesIndex,
    store as quoteStore,
} from '@/routes/admin/quotes';

defineOptions({ layout: AdminLayout });

type ProductResult = {
    id: string;
    name: string;
    price: string | null;
};

defineProps<{
    users: { id: string; name: string; email: string }[];
    productResults?: ProductResult[];
}>();

const { t } = useT();

const userValue = ref('none');
const productSearch = ref('');

const form = useForm({
    user_id: null as string | null,
    notes: '',
    items: [] as {
        product_id: string | null;
        name: string;
        quantity: number;
        unit_price: string;
    }[],
});

function searchProducts() {
    router.get(
        quoteCreate().url,
        { search: productSearch.value },
        { preserveState: true, preserveScroll: true, only: ['productResults'] },
    );
}

function addProduct(product: ProductResult) {
    form.items.push({
        product_id: product.id,
        name: product.name,
        quantity: 1,
        unit_price: product.price ?? '0',
    });
    productSearch.value = '';
}

function addBlankItem() {
    form.items.push({
        product_id: null,
        name: '',
        quantity: 1,
        unit_price: '0',
    });
}

function removeItem(index: number) {
    form.items.splice(index, 1);
}

function submit() {
    form.transform((data) => ({
        ...data,
        user_id: userValue.value === 'none' ? null : userValue.value,
    })).post(quoteStore().url);
}
</script>

<template>
    <Head :title="t('app.admin.quotes.new')" />

    <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold">{{ t('app.admin.quotes.new') }}</h1>
        <Button variant="outline" as-child>
            <Link :href="quotesIndex().url">{{
                t('app.admin.common.back')
            }}</Link>
        </Button>
    </div>

    <form class="grid gap-6 lg:grid-cols-3" @submit.prevent="submit">
        <div class="space-y-6 lg:col-span-2">
            <Card>
                <CardHeader>
                    <CardTitle>{{ t('app.admin.quotes.items') }}</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="relative">
                        <Input
                            v-model="productSearch"
                            type="search"
                            :placeholder="t('app.admin.quotes.search_product')"
                            @input="searchProducts"
                        />
                        <ul
                            v-if="
                                productSearch !== '' &&
                                (productResults?.length ?? 0) > 0
                            "
                            class="absolute z-10 mt-1 w-full rounded-md border bg-popover shadow-md"
                        >
                            <li
                                v-for="product in productResults"
                                :key="product.id"
                            >
                                <button
                                    type="button"
                                    class="flex w-full items-center justify-between px-3 py-2 text-left text-sm hover:bg-accent"
                                    @click="addProduct(product)"
                                >
                                    <span>{{ product.name }}</span>
                                    <span
                                        v-if="product.price"
                                        class="text-muted-foreground"
                                    >
                                        R$ {{ product.price }}
                                    </span>
                                </button>
                            </li>
                        </ul>
                    </div>

                    <InputError :message="form.errors.items" />

                    <div
                        v-for="(item, index) in form.items"
                        :key="index"
                        class="grid gap-3 rounded-md border p-3 sm:grid-cols-[1fr_6rem_8rem_auto]"
                    >
                        <div class="grid gap-1">
                            <Label :for="`item-name-${index}`" class="text-xs">
                                {{ t('app.admin.quotes.item_name') }}
                            </Label>
                            <Input
                                :id="`item-name-${index}`"
                                v-model="item.name"
                                required
                            />
                            <InputError
                                :message="form.errors[`items.${index}.name`]"
                            />
                        </div>
                        <div class="grid gap-1">
                            <Label :for="`item-qty-${index}`" class="text-xs">
                                {{ t('app.admin.quotes.quantity') }}
                            </Label>
                            <Input
                                :id="`item-qty-${index}`"
                                v-model.number="item.quantity"
                                type="number"
                                min="1"
                                required
                            />
                            <InputError
                                :message="
                                    form.errors[`items.${index}.quantity`]
                                "
                            />
                        </div>
                        <div class="grid gap-1">
                            <Label :for="`item-price-${index}`" class="text-xs">
                                {{ t('app.admin.quotes.unit_price') }}
                            </Label>
                            <Input
                                :id="`item-price-${index}`"
                                v-model="item.unit_price"
                                type="number"
                                min="0"
                                step="0.01"
                                required
                            />
                            <InputError
                                :message="
                                    form.errors[`items.${index}.unit_price`]
                                "
                            />
                        </div>
                        <div class="flex items-end">
                            <Button
                                type="button"
                                variant="destructive"
                                size="icon"
                                :aria-label="t('app.admin.quotes.remove_item')"
                                @click="removeItem(index)"
                            >
                                <Trash2 />
                            </Button>
                        </div>
                    </div>

                    <Button
                        type="button"
                        variant="outline"
                        @click="addBlankItem"
                    >
                        <Plus />
                        {{ t('app.admin.quotes.add_item') }}
                    </Button>
                </CardContent>
            </Card>
        </div>

        <div class="space-y-6">
            <Card>
                <CardContent class="space-y-4 pt-6">
                    <div class="grid gap-2">
                        <Label>{{ t('app.admin.quotes.customer') }}</Label>
                        <Select v-model="userValue">
                            <SelectTrigger class="w-full">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="none">{{
                                    t('app.admin.quotes.no_customer')
                                }}</SelectItem>
                                <SelectItem
                                    v-for="user in users"
                                    :key="user.id"
                                    :value="user.id"
                                >
                                    {{ user.name }} — {{ user.email }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.user_id" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="quote-notes">{{
                            t('app.admin.quotes.notes')
                        }}</Label>
                        <textarea
                            id="quote-notes"
                            v-model="form.notes"
                            rows="4"
                            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                        ></textarea>
                        <InputError :message="form.errors.notes" />
                    </div>
                    <Button
                        type="submit"
                        class="w-full"
                        :disabled="form.processing || form.items.length === 0"
                    >
                        {{
                            form.processing
                                ? t('app.admin.common.saving')
                                : t('app.admin.common.save')
                        }}
                    </Button>
                </CardContent>
            </Card>
        </div>
    </form>
</template>
