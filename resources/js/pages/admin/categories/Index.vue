<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import {
    ArrowDown,
    ArrowUp,
    Pencil,
    Plus,
    RotateCcw,
    Trash2,
} from '@lucide/vue';
import { ref } from 'vue';
import ConfirmDeleteDialog from '@/components/admin/ConfirmDeleteDialog.vue';
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
    filters: { search: string | null; status: string | null };
}>();

const { t } = useT();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? 'all');
const formOpen = ref(false);
const editing = ref<Category | null>(null);
const statusValue = ref('published');
const deleting = ref<Category | null>(null);

function applyFilters() {
    router.get(
        categoriesIndex().url,
        {
            search: search.value || undefined,
            status: status.value !== 'all' ? status.value : undefined,
        },
        { preserveState: true, preserveScroll: true },
    );
}

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
    const active = props.categories;
    const ids = active.map((category) => category.id);
    const target = index + delta;

    if (target < 0 || target >= ids.length) {
        return;
    }

    [ids[index], ids[target]] = [ids[target], ids[index]];

    router.post(categoryReorder().url, { ids }, { preserveScroll: true });
}

function confirmDelete() {
    if (!deleting.value) {
        return;
    }

    router.delete(categoryDestroy(deleting.value.slug).url, {
        preserveScroll: true,
        onFinish: () => (deleting.value = null),
    });
}

function restore(category: Category) {
    router.post(
        categoryRestore(category.slug).url,
        {},
        { preserveScroll: true },
    );
}
</script>

<template>
    <Head :title="t('app.admin.categories.title')" />

    <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold">
            {{ t('app.admin.categories.title') }}
        </h1>
        <Button type="button" @click="openCreate">
            <Plus />
            {{ t('app.admin.categories.new') }}
        </Button>
    </div>

    <div class="flex items-center gap-3">
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
                <SelectItem value="all">
                    {{ t('app.admin.common.filter_all') }}
                </SelectItem>
                <SelectItem value="draft">
                    {{ t('app.admin.status.draft') }}
                </SelectItem>
                <SelectItem value="published">
                    {{ t('app.admin.status.published') }}
                </SelectItem>
                <SelectItem value="archived">
                    {{ t('app.admin.status.archived') }}
                </SelectItem>
                <SelectItem value="trashed">
                    {{ t('app.admin.common.filter_trashed') }}
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
                        {{ t('app.admin.common.status') }}
                    </th>
                    <th class="px-4 py-3 font-medium">
                        {{ t('app.admin.categories.products_count') }}
                    </th>
                    <th class="px-4 py-3 text-right font-medium">
                        {{ t('app.admin.common.actions') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="categories.length === 0">
                    <td
                        colspan="4"
                        class="px-4 py-8 text-center text-muted-foreground"
                    >
                        {{ t('app.admin.common.empty') }}
                    </td>
                </tr>
                <tr
                    v-for="(category, index) in categories"
                    :key="category.id"
                    class="border-b last:border-b-0"
                    :class="{ 'opacity-60': category.deleted }"
                >
                    <td class="px-4 py-3">
                        <span class="font-medium">{{ category.name }}</span>
                        <span class="ml-2 text-xs text-muted-foreground"
                            >/{{ category.slug }}</span
                        >
                    </td>
                    <td class="px-4 py-3">
                        <Badge v-if="category.deleted" variant="destructive">
                            {{ t('app.admin.common.deleted_badge') }}
                        </Badge>
                        <Badge
                            v-else
                            :variant="
                                category.status === 'published'
                                    ? 'default'
                                    : 'secondary'
                            "
                        >
                            {{ t(`app.admin.status.${category.status}`) }}
                        </Badge>
                    </td>
                    <td class="px-4 py-3">{{ category.productsCount }}</td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-1">
                            <template v-if="!category.deleted">
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="icon"
                                    :disabled="index === 0"
                                    :aria-label="t('app.admin.common.move_up')"
                                    @click="move(index, -1)"
                                >
                                    <ArrowUp />
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="icon"
                                    :disabled="index === categories.length - 1"
                                    :aria-label="
                                        t('app.admin.common.move_down')
                                    "
                                    @click="move(index, 1)"
                                >
                                    <ArrowDown />
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="icon"
                                    :aria-label="t('app.admin.common.edit')"
                                    @click="openEdit(category)"
                                >
                                    <Pencil />
                                </Button>
                                <Button
                                    type="button"
                                    variant="destructive"
                                    size="icon"
                                    :aria-label="t('app.admin.common.delete')"
                                    @click="deleting = category"
                                >
                                    <Trash2 />
                                </Button>
                            </template>
                            <Button
                                v-else
                                type="button"
                                variant="outline"
                                @click="restore(category)"
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
                        <SelectTrigger>
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

    <ConfirmDeleteDialog
        :open="deleting !== null"
        @update:open="deleting = $event ? deleting : null"
        @confirm="confirmDelete"
    />
</template>
