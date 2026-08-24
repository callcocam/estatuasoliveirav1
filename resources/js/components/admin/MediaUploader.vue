<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ArrowDown, ArrowUp, ImagePlus, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useT } from '@/composables/useT';
import {
    destroy as mediaDestroy,
    reorder as mediaReorder,
    store as mediaStore,
    update as mediaUpdate,
} from '@/routes/admin/media';

export type MediaItem = {
    id: string;
    url: string;
    alt: string | null;
};

const props = defineProps<{
    mediableType: 'product' | 'slider' | 'category';
    mediableId: string;
    media: MediaItem[];
}>();

const { t } = useT();

const fileInput = ref<HTMLInputElement | null>(null);
const uploading = ref(false);
const dragging = ref(false);

function openPicker() {
    fileInput.value?.click();
}

function handleFiles(files: FileList | null) {
    if (!files || files.length === 0) {
        return;
    }

    uploadNext(Array.from(files), 0);
}

function uploadNext(files: File[], index: number) {
    if (index >= files.length) {
        uploading.value = false;

        if (fileInput.value) {
            fileInput.value.value = '';
        }

        return;
    }

    uploading.value = true;

    router.post(
        mediaStore().url,
        {
            file: files[index],
            mediable_type: props.mediableType,
            mediable_id: props.mediableId,
        },
        {
            forceFormData: true,
            preserveScroll: true,
            onFinish: () => uploadNext(files, index + 1),
        },
    );
}

function handleDrop(event: DragEvent) {
    dragging.value = false;
    handleFiles(event.dataTransfer?.files ?? null);
}

function move(index: number, delta: number) {
    const ids = props.media.map((item) => item.id);
    const target = index + delta;

    if (target < 0 || target >= ids.length) {
        return;
    }

    [ids[index], ids[target]] = [ids[target], ids[index]];

    router.post(mediaReorder().url, { ids }, { preserveScroll: true });
}

function updateAlt(item: MediaItem, event: Event) {
    const alt = (event.target as HTMLInputElement).value;

    if ((item.alt ?? '') === alt) {
        return;
    }

    router.patch(mediaUpdate(item.id).url, { alt }, { preserveScroll: true });
}

function remove(item: MediaItem) {
    router.delete(mediaDestroy(item.id).url, { preserveScroll: true });
}
</script>

<template>
    <div class="space-y-4">
        <button
            type="button"
            class="flex w-full flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed p-6 text-sm text-muted-foreground transition-colors hover:border-primary hover:text-foreground"
            :class="dragging ? 'border-primary bg-accent' : 'border-border'"
            @click="openPicker"
            @dragover.prevent="dragging = true"
            @dragleave.prevent="dragging = false"
            @drop.prevent="handleDrop"
        >
            <ImagePlus class="size-6" />
            <span>{{
                uploading
                    ? t('app.admin.media.uploading')
                    : t('app.admin.media.drop_hint')
            }}</span>
        </button>
        <input
            ref="fileInput"
            type="file"
            accept="image/jpeg,image/png,image/webp"
            multiple
            class="hidden"
            @change="handleFiles(($event.target as HTMLInputElement).files)"
        />

        <ul
            v-if="media.length > 0"
            class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4"
        >
            <li
                v-for="(item, index) in media"
                :key="item.id"
                class="space-y-2 rounded-lg border p-2"
            >
                <div
                    class="relative aspect-square overflow-hidden rounded-md bg-muted"
                >
                    <img
                        :src="item.url"
                        :alt="item.alt ?? ''"
                        class="size-full object-cover"
                    />
                    <span
                        v-if="index === 0"
                        class="absolute top-1 left-1 rounded bg-primary px-1.5 py-0.5 text-xs text-primary-foreground"
                    >
                        {{ t('app.admin.media.cover') }}
                    </span>
                </div>
                <Input
                    type="text"
                    :model-value="item.alt ?? ''"
                    :placeholder="t('app.admin.media.alt_placeholder')"
                    @blur="updateAlt(item, $event)"
                />
                <div class="flex items-center justify-between gap-1">
                    <div class="flex gap-1">
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
                            :disabled="index === media.length - 1"
                            :aria-label="t('app.admin.common.move_down')"
                            @click="move(index, 1)"
                        >
                            <ArrowDown />
                        </Button>
                    </div>
                    <Button
                        type="button"
                        variant="destructive"
                        size="icon"
                        :aria-label="t('app.admin.media.remove')"
                        @click="remove(item)"
                    >
                        <Trash2 />
                    </Button>
                </div>
            </li>
        </ul>
    </div>
</template>
