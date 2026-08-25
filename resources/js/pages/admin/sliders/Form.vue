<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import MediaUploader from '@/components/admin/MediaUploader.vue';
import type { MediaItem } from '@/components/admin/MediaUploader.vue';
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
    index as slidersIndex,
    store as sliderStore,
    update as sliderUpdate,
} from '@/routes/admin/sliders';

defineOptions({ layout: AdminLayout });

type SliderDetail = {
    id: string;
    title: string;
    subtitle: string | null;
    description: string | null;
    ctaLabel: string | null;
    ctaUrl: string | null;
    status: string;
    sortOrder: number;
    media: MediaItem[];
};

const props = defineProps<{
    slider: SliderDetail | null;
}>();

const { t } = useT();

const statusValue = ref(props.slider?.status ?? 'draft');
</script>

<template>
    <Head
        :title="
            slider ? t('app.admin.sliders.edit') : t('app.admin.sliders.new')
        "
    />

    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold">
            {{
                slider
                    ? t('app.admin.sliders.edit')
                    : t('app.admin.sliders.new')
            }}
        </h1>
        <Button variant="outline" as-child>
            <Link :href="slidersIndex().url">{{
                t('app.admin.common.back')
            }}</Link>
        </Button>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <Card class="lg:col-span-2">
            <CardContent class="pt-6">
                <Form
                    v-bind="
                        slider
                            ? sliderUpdate.form(slider.id)
                            : sliderStore.form()
                    "
                    class="space-y-4"
                    #default="{ errors, processing }"
                >
                    <div class="grid gap-2">
                        <Label for="slider-title">{{
                            t('app.admin.sliders.fields.title')
                        }}</Label>
                        <Input
                            id="slider-title"
                            name="title"
                            :default-value="slider?.title ?? ''"
                            required
                        />
                        <InputError :message="errors.title" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="slider-subtitle">{{
                            t('app.admin.sliders.fields.subtitle')
                        }}</Label>
                        <Input
                            id="slider-subtitle"
                            name="subtitle"
                            :default-value="slider?.subtitle ?? ''"
                        />
                        <InputError :message="errors.subtitle" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="slider-description">{{
                            t('app.admin.sliders.fields.description')
                        }}</Label>
                        <textarea
                            id="slider-description"
                            name="description"
                            rows="3"
                            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                            :value="slider?.description ?? ''"
                        ></textarea>
                        <InputError :message="errors.description" />
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="slider-cta-label">{{
                                t('app.admin.sliders.fields.cta_label')
                            }}</Label>
                            <Input
                                id="slider-cta-label"
                                name="cta_label"
                                :default-value="slider?.ctaLabel ?? ''"
                            />
                            <InputError :message="errors.cta_label" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="slider-cta-url">{{
                                t('app.admin.sliders.fields.cta_url')
                            }}</Label>
                            <Input
                                id="slider-cta-url"
                                name="cta_url"
                                :default-value="slider?.ctaUrl ?? ''"
                            />
                            <InputError :message="errors.cta_url" />
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <Label>{{
                            t('app.admin.sliders.fields.status')
                        }}</Label>
                        <Select v-model="statusValue" name="status">
                            <SelectTrigger class="w-full">
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
                    <Button type="submit" :disabled="processing">
                        {{
                            processing
                                ? t('app.admin.common.saving')
                                : t('app.admin.common.save')
                        }}
                    </Button>
                </Form>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>{{ t('app.admin.sliders.image') }}</CardTitle>
            </CardHeader>
            <CardContent>
                <MediaUploader
                    v-if="slider"
                    mediable-type="slider"
                    :mediable-id="slider.id"
                    :media="slider.media"
                />
                <p v-else class="text-sm text-muted-foreground">
                    {{ t('app.admin.sliders.save_before_image') }}
                </p>
            </CardContent>
        </Card>
    </div>
</template>
