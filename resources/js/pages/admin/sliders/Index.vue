<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowDown, ArrowUp, Plus } from '@lucide/vue';
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
import { useT } from '@/composables/useT';
import AdminLayout from '@/layouts/AdminLayout.vue';
import {
    create as sliderCreate,
    destroy as sliderDestroy,
    edit as sliderEdit,
    index as slidersIndex,
    reorder as sliderReorder,
    restore as sliderRestore,
} from '@/routes/admin/sliders';
import type { ResourceAbilities } from '@/types/admin';

defineOptions({ layout: AdminLayout });

type SliderRow = {
    id: string;
    title: string;
    subtitle: string | null;
    status: string;
    sortOrder: number;
    image: string | null;
    deleted: boolean;
};

const props = defineProps<{
    sliders: SliderRow[];
    filters: { search: string; status: string; trashed: string };
    can: ResourceAbilities;
}>();

const { t } = useT();

const statusOptions = [
    { value: 'all', label: t('app.admin.common.filter_all') },
    { value: 'draft', label: t('app.admin.status.draft') },
    { value: 'published', label: t('app.admin.status.published') },
    { value: 'archived', label: t('app.admin.status.archived') },
];

function move(index: number, delta: number) {
    const ids = props.sliders.map((slider) => slider.id);
    const target = index + delta;

    if (target < 0 || target >= ids.length) {
        return;
    }

    [ids[index], ids[target]] = [ids[target], ids[index]];

    router.post(sliderReorder().url, { ids }, { preserveScroll: true });
}
</script>

<template>
    <Head :title="t('app.admin.sliders.title')" />

    <ListPage
        :title="t('app.admin.sliders.title')"
        :loading="false"
        :empty="sliders.length === 0"
        :columns="3"
    >
        <template #actions>
            <Button v-if="can.create" as-child>
                <Link :href="sliderCreate().url">
                    <Plus class="size-4" />
                    {{ t('app.admin.sliders.new') }}
                </Link>
            </Button>
        </template>

        <template #filters>
            <ListFiltersBar
                :index-url="slidersIndex().url"
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
                        <SelectTrigger class="w-44" :aria-label="t('app.admin.common.status')">
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
            <th class="px-4 py-3 font-medium">{{ t('app.admin.sliders.fields.title') }}</th>
            <th class="px-4 py-3 font-medium">{{ t('app.admin.common.status') }}</th>
            <th class="px-4 py-3 text-right font-medium">{{ t('app.admin.common.actions') }}</th>
        </template>

        <template #body>
            <tr
                v-for="(slider, index) in sliders"
                :key="slider.id"
                class="border-b border-border last:border-b-0"
                :class="{ 'opacity-60': slider.deleted }"
            >
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <img
                            v-if="slider.image"
                            :src="slider.image"
                            :alt="slider.title"
                            class="h-10 w-16 rounded-md object-cover"
                        />
                        <div
                            v-else
                            class="h-10 w-16 rounded-md bg-muted"
                            aria-hidden="true"
                        />
                        <div class="min-w-0">
                            <p class="truncate font-medium text-foreground">{{ slider.title }}</p>
                            <p v-if="slider.subtitle" class="truncate text-xs text-muted-foreground">
                                {{ slider.subtitle }}
                            </p>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3">
                    <div class="flex flex-wrap items-center gap-1">
                        <Badge :variant="slider.status === 'published' ? 'default' : 'secondary'">
                            {{ t(`app.admin.status.${slider.status}`) }}
                        </Badge>
                        <Badge v-if="slider.deleted" variant="destructive">
                            {{ t('app.admin.common.deleted_badge') }}
                        </Badge>
                    </div>
                </td>
                <td class="px-4 py-3">
                    <ColumnActions
                        :trashed="slider.deleted"
                        :edit-href="sliderEdit(slider.id).url"
                        :delete-href="sliderDestroy(slider.id).url"
                        :restore-href="sliderRestore(slider.id).url"
                        :can-update="can.update"
                        :can-delete="can.delete"
                    >
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
                            :disabled="index === sliders.length - 1"
                            :aria-label="t('app.admin.common.move_down')"
                            @click="move(index, 1)"
                        >
                            <ArrowDown class="size-4" />
                        </Button>
                    </ColumnActions>
                </td>
            </tr>
        </template>
    </ListPage>
</template>
