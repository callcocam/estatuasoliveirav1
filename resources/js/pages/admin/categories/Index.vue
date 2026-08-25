<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import { ArrowDown, ArrowUp, Pencil, Plus } from '@lucide/vue';
import { ref } from 'vue';
import DeleteButton from '@/components/admin/DeleteButton.vue';
import ListFiltersBar from '@/components/admin/ListFiltersBar.vue';
import ListPage from '@/components/admin/ListPage.vue';
import RestoreButton from '@/components/admin/RestoreButton.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
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
    destroy as categoryDestroy,
    index as categoriesIndex,
    reorder as categoryReorder,
    restore as categoryRestore,
    store as categoryStore,
    update as categoryUpdate,
} from '@/routes/admin/categories';
import type { ResourceAbilities } from '@/types/admin';

defineOptions({ layout: AdminLayout });

type Category = {
    id: string;
    name: string;
    slug: string;
    description: string | null;
    status: string;
    sortOrder: number;
    productsCount: number;
    deleted: boolean;
};

const props = defineProps<{
    categories: Category[];
    filters: { search: string; status: string; trashed: string };
    can: ResourceAbilities;
}>();

const { t } = useT();

const formOpen = ref(false);
const editing = ref<Category | null>(null);
const statusValue = ref('published');

const statusOptions = [
    { value: 'all', label: t('app.admin.common.filter_all') },
    { value: 'draft', label: t('app.admin.status.draft') },
    { value: 'published', label: t('app.admin.status.published') },
    { value: 'archived', label: t('app.admin.status.archived') },
];

function openCreate() {
    editing.value = null;
    statusValue.value = 'published';
    formOpen.value = true;
}

function openEdit(category: Category) {
    editing.value = category;
    statusValue.value = category.status;
    formOpen.value = true;
}

function move(index: number, delta: number) {
    const ids = props.categories.map((category) => category.id);
    const target = index + delta;

    if (target < 0 || target >= ids.length) {
        return;
    }

    [ids[index], ids[target]] = [ids[target], ids[index]];

    router.post(categoryReorder().url, { ids }, { preserveScroll: true });
}
</script>

<template>
    <Head :title="t('app.admin.categories.title')" />

    <ListPage
        :title="t('app.admin.categories.title')"
        :loading="false"
        :empty="categories.length === 0"
        :columns="4"
    >
        <template #actions>
            <Button v-if="can.create" type="button" @click="openCreate">
                <Plus class="size-4" />
                {{ t('app.admin.categories.new') }}
            </Button>
        </template>

        <template #filters>
            <ListFiltersBar
                :index-url="categoriesIndex().url"
                :filters="{
                    search: filters.search,
                    status: filters.status || 'all',
                    trashed: filters.trashed,
                }"
            >
                <template #default="{ values, set }">
                    <Select
                        :model-value="values.status"
                        @update:model-value="set('status', String($event ?? 'all'))"
                    >
                        <SelectTrigger class="w-44" :aria-label="t('app.admin.categories.fields.status')">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="option in statusOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </template>
            </ListFiltersBar>
        </template>

        <template #head>
            <th class="px-4 py-3 font-medium">{{ t('app.admin.common.name') }}</th>
            <th class="px-4 py-3 font-medium">{{ t('app.admin.common.status') }}</th>
            <th class="hidden px-4 py-3 text-center font-medium md:table-cell">
                {{ t('app.admin.categories.products_count') }}
            </th>
            <th class="px-4 py-3 text-right font-medium">{{ t('app.admin.common.actions') }}</th>
        </template>

        <template #body>
            <tr
                v-for="(category, index) in categories"
                :key="category.id"
                class="border-b border-border last:border-b-0"
                :class="{ 'opacity-60': category.deleted }"
            >
                <td class="px-4 py-3">
                    <p class="font-medium text-foreground">{{ category.name }}</p>
                    <p class="text-xs text-muted-foreground">{{ category.slug }}</p>
                </td>
                <td class="px-4 py-3">
                    <div class="flex flex-wrap items-center gap-1">
                        <Badge :variant="category.status === 'published' ? 'default' : 'secondary'">
                            {{ t(`app.admin.status.${category.status}`) }}
                        </Badge>
                        <Badge v-if="category.deleted" variant="destructive">
                            {{ t('app.admin.common.deleted_badge') }}
                        </Badge>
                    </div>
                </td>
                <td class="hidden px-4 py-3 text-center md:table-cell">
                    {{ category.productsCount }}
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center justify-end gap-2">
                        <template v-if="!category.deleted">
                            <Button
                                v-if="can.update"
                                size="icon"
                                variant="outline"
                                type="button"
                                :disabled="index === 0"
                                :aria-label="t('app.admin.common.move_up')"
                                @click="move(index, -1)"
                            >
                                <ArrowUp class="size-4" />
                            </Button>
                            <Button
                                v-if="can.update"
                                size="icon"
                                variant="outline"
                                type="button"
                                :disabled="index === categories.length - 1"
                                :aria-label="t('app.admin.common.move_down')"
                                @click="move(index, 1)"
                            >
                                <ArrowDown class="size-4" />
                            </Button>
                            <Button
                                v-if="can.update"
                                size="icon"
                                variant="outline"
                                type="button"
                                :aria-label="t('app.admin.common.edit')"
                                @click="openEdit(category)"
                            >
                                <Pencil class="size-4" />
                            </Button>
                            <DeleteButton
                                v-if="can.delete"
                                :href="categoryDestroy(category.slug).url"
                            />
                        </template>
                        <template v-else>
                            <RestoreButton
                                v-if="can.delete"
                                :href="categoryRestore(category.slug).url"
                            />
                            <DeleteButton
                                v-if="can.delete"
                                :href="categoryDestroy(category.slug).url"
                                permanent
                            />
                        </template>
                    </div>
                </td>
            </tr>
        </template>
    </ListPage>

    <Dialog v-model:open="formOpen">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>
                    {{
                        editing
                            ? t('app.admin.categories.edit')
                            : t('app.admin.categories.new')
                    }}
                </DialogTitle>
            </DialogHeader>
            <Form
                :key="editing?.id ?? 'create'"
                v-bind="
                    editing
                        ? categoryUpdate.form(editing.slug)
                        : categoryStore.form()
                "
                class="space-y-4"
                :options="{ preserveScroll: true }"
                @success="formOpen = false"
                #default="{ errors, processing }"
            >
                <div class="grid gap-2">
                    <Label for="category-name">{{
                        t('app.admin.categories.fields.name')
                    }}</Label>
                    <Input
                        id="category-name"
                        name="name"
                        :default-value="editing?.name ?? ''"
                        required
                    />
                    <InputError :message="errors.name" />
                </div>
                <div class="grid gap-2">
                    <Label for="category-slug">{{
                        t('app.admin.categories.fields.slug')
                    }}</Label>
                    <Input
                        id="category-slug"
                        name="slug"
                        :default-value="editing?.slug ?? ''"
                    />
                    <InputError :message="errors.slug" />
                </div>
                <div class="grid gap-2">
                    <Label for="category-description">{{
                        t('app.admin.categories.fields.description')
                    }}</Label>
                    <textarea
                        id="category-description"
                        name="description"
                        rows="3"
                        class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                        :value="editing?.description ?? ''"
                    ></textarea>
                    <InputError :message="errors.description" />
                </div>
                <div class="grid gap-2">
                    <Label>{{ t('app.admin.categories.fields.status') }}</Label>
                    <Select v-model="statusValue" name="status">
                        <SelectTrigger class="w-full">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="published">{{
                                t('app.admin.status.published')
                            }}</SelectItem>
                            <SelectItem value="draft">{{
                                t('app.admin.status.draft')
                            }}</SelectItem>
                            <SelectItem value="archived">{{
                                t('app.admin.status.archived')
                            }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="errors.status" />
                </div>
                <div class="flex justify-end gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        @click="formOpen = false"
                    >
                        {{ t('app.admin.common.cancel') }}
                    </Button>
                    <Button type="submit" :disabled="processing">
                        {{
                            processing
                                ? t('app.admin.common.saving')
                                : t('app.admin.common.save')
                        }}
                    </Button>
                </div>
            </Form>
        </DialogContent>
    </Dialog>
</template>
