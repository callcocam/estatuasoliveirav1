<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { onBeforeUnmount, reactive, watch } from 'vue';
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
import type { TrashedFilter } from '@/types/admin';

type FilterValues = Record<string, string>;

const props = withDefaults(
    defineProps<{
        indexUrl: string;
        filters: FilterValues;
        searchPlaceholder?: string;
        showTrashed?: boolean;
        perPageOptions?: number[];
    }>(),
    {
        showTrashed: true,
        perPageOptions: () => [10, 15, 25, 50],
    },
);

const { t } = useT();

const values = reactive<FilterValues>({ ...props.filters });

let searchTimeout: ReturnType<typeof setTimeout> | null = null;

function submit(): void {
    const query = Object.fromEntries(
        Object.entries(values).filter(
            ([, value]) =>
                value !== '' && value !== 'all' && value !== 'without',
        ),
    );

    router.get(props.indexUrl, query, {
        preserveState: true,
        preserveScroll: true,
    });
}

function set(key: string, value: string): void {
    values[key] = value;
    submit();
}

function clear(): void {
    for (const key of Object.keys(values)) {
        values[key] = key === 'per_page' ? values[key] : '';
    }

    router.get(
        props.indexUrl,
        {},
        { preserveState: true, preserveScroll: true },
    );
}

watch(
    () => values.search,
    () => {
        if (searchTimeout !== null) {
            clearTimeout(searchTimeout);
        }

        searchTimeout = setTimeout(submit, 400);
    },
);

onBeforeUnmount(() => {
    if (searchTimeout !== null) {
        clearTimeout(searchTimeout);
    }
});

const trashedOptions: { value: TrashedFilter; label: string }[] = [
    { value: 'without', label: t('app.admin.common.filter_trashed_without') },
    { value: 'only', label: t('app.admin.common.filter_trashed_only') },
    { value: 'with', label: t('app.admin.common.filter_trashed_with') },
];
</script>

<template>
    <div class="flex flex-wrap items-center gap-2">
        <Input
            v-if="'search' in values"
            v-model="values.search"
            type="search"
            :placeholder="
                searchPlaceholder ?? t('app.admin.common.search_placeholder')
            "
            class="max-w-xs"
        />

        <slot :values="values" :set="set" :submit="submit" />

        <Select
            v-if="showTrashed"
            :model-value="values.trashed || 'without'"
            @update:model-value="set('trashed', String($event ?? 'without'))"
        >
            <SelectTrigger
                class="w-44"
                :aria-label="t('app.admin.common.filter_trashed')"
            >
                <SelectValue />
            </SelectTrigger>
            <SelectContent>
                <SelectItem
                    v-for="option in trashedOptions"
                    :key="option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </SelectItem>
            </SelectContent>
        </Select>

        <Select
            v-if="'per_page' in values"
            :model-value="values.per_page || '15'"
            @update:model-value="set('per_page', String($event ?? '15'))"
        >
            <SelectTrigger
                class="w-28"
                :aria-label="t('app.admin.common.per_page')"
            >
                <SelectValue />
            </SelectTrigger>
            <SelectContent>
                <SelectItem
                    v-for="option in perPageOptions"
                    :key="option"
                    :value="String(option)"
                >
                    {{ option }}
                </SelectItem>
            </SelectContent>
        </Select>

        <Button variant="ghost" type="button" @click="clear">
            {{ t('app.admin.common.clear_filters') }}
        </Button>
    </div>
</template>
