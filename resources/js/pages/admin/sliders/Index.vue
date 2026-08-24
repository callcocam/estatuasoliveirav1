<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowDown, ArrowUp, Pencil, Plus, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import ConfirmDeleteDialog from '@/components/admin/ConfirmDeleteDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useT } from '@/composables/useT';
import AdminLayout from '@/layouts/AdminLayout.vue';
import {
    create as sliderCreate,
    destroy as sliderDestroy,
    edit as sliderEdit,
    reorder as sliderReorder,
} from '@/routes/admin/sliders';

defineOptions({ layout: AdminLayout });

type SliderRow = {
    id: string;
    title: string;
    subtitle: string | null;
    status: string;
    sortOrder: number;
    image: string | null;
};

const props = defineProps<{
    sliders: SliderRow[];
}>();

const { t } = useT();

const deleting = ref<SliderRow | null>(null);

function move(index: number, delta: number) {
    const ids = props.sliders.map((slider) => slider.id);
    const target = index + delta;

    if (target < 0 || target >= ids.length) {
        return;
    }

    [ids[index], ids[target]] = [ids[target], ids[index]];

    router.post(sliderReorder().url, { ids }, { preserveScroll: true });
}

function confirmDelete() {
    if (!deleting.value) {
        return;
    }

    router.delete(sliderDestroy(deleting.value.id).url, {
        preserveScroll: true,
        onFinish: () => (deleting.value = null),
    });
}
</script>

<template>
    <Head :title="t('app.admin.sliders.title')" />

    <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold">
            {{ t('app.admin.sliders.title') }}
        </h1>
        <Button as-child>
            <Link :href="sliderCreate().url">
                <Plus />
                {{ t('app.admin.sliders.new') }}
            </Link>
        </Button>
    </div>

    <div class="overflow-x-auto rounded-lg border">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b bg-muted/50 text-left">
                    <th class="px-4 py-3 font-medium">
                        {{ t('app.admin.sliders.fields.title') }}
                    </th>
                    <th class="px-4 py-3 font-medium">
                        {{ t('app.admin.common.status') }}
                    </th>
                    <th class="px-4 py-3 text-right font-medium">
                        {{ t('app.admin.common.actions') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="sliders.length === 0">
                    <td
                        colspan="3"
                        class="px-4 py-8 text-center text-muted-foreground"
                    >
                        {{ t('app.admin.common.empty') }}
                    </td>
                </tr>
                <tr
                    v-for="(slider, index) in sliders"
                    :key="slider.id"
                    class="border-b last:border-b-0"
                >
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <img
                                v-if="slider.image"
                                :src="slider.image"
                                alt=""
                                class="h-10 w-16 rounded-md object-cover"
                            />
                            <div
                                v-else
                                class="h-10 w-16 rounded-md bg-muted"
                                aria-hidden="true"
                            ></div>
                            <div class="min-w-0">
                                <p class="truncate font-medium">
                                    {{ slider.title }}
                                </p>
                                <p
                                    class="truncate text-xs text-muted-foreground"
                                >
                                    {{
                                        slider.subtitle ??
                                        t('app.admin.common.none')
                                    }}
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <Badge
                            :variant="
                                slider.status === 'published'
                                    ? 'default'
                                    : 'secondary'
                            "
                        >
                            {{ t(`app.admin.status.${slider.status}`) }}
                        </Badge>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-1">
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
                                :disabled="index === sliders.length - 1"
                                :aria-label="t('app.admin.common.move_down')"
                                @click="move(index, 1)"
                            >
                                <ArrowDown />
                            </Button>
                            <Button
                                variant="outline"
                                size="icon"
                                as-child
                                :aria-label="t('app.admin.common.edit')"
                            >
                                <Link :href="sliderEdit(slider.id).url">
                                    <Pencil />
                                </Link>
                            </Button>
                            <Button
                                type="button"
                                variant="destructive"
                                size="icon"
                                :aria-label="t('app.admin.common.delete')"
                                @click="deleting = slider"
                            >
                                <Trash2 />
                            </Button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <ConfirmDeleteDialog
        :open="deleting !== null"
        @update:open="deleting = $event ? deleting : null"
        @confirm="confirmDelete"
    />
</template>
