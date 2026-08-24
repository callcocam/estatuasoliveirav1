<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useT } from '@/composables/useT';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { update as settingsUpdate } from '@/routes/admin/settings';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    values: Record<string, string | null>;
    logoUrl: string | null;
    themes: { value: string; label: string }[];
}>();

const { t } = useT();

const themeValue = ref(props.values.site_default_theme ?? 'stone');
</script>

<template>
    <Head :title="t('app.admin.settings.title')" />

    <h1 class="text-2xl font-semibold">{{ t('app.admin.settings.title') }}</h1>

    <Form
        v-bind="settingsUpdate.form()"
        class="space-y-6"
        :options="{ preserveScroll: true }"
        #default="{ errors, processing }"
    >
        <Card>
            <CardHeader>
                <CardTitle>{{ t('app.admin.settings.company') }}</CardTitle>
            </CardHeader>
            <CardContent class="grid gap-4 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="company-name">{{
                        t('app.admin.settings.fields.company_name')
                    }}</Label>
                    <Input
                        id="company-name"
                        name="company_name"
                        :default-value="values.company_name ?? ''"
                        required
                    />
                    <InputError :message="errors.company_name" />
                </div>
                <div class="grid gap-2">
                    <Label for="company-document">{{
                        t('app.admin.settings.fields.company_document')
                    }}</Label>
                    <Input
                        id="company-document"
                        name="company_document"
                        :default-value="values.company_document ?? ''"
                    />
                    <InputError :message="errors.company_document" />
                </div>
                <div class="grid gap-2 sm:col-span-2">
                    <Label for="company-about">{{
                        t('app.admin.settings.fields.company_about')
                    }}</Label>
                    <textarea
                        id="company-about"
                        name="company_about"
                        rows="4"
                        class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                        :value="values.company_about ?? ''"
                    ></textarea>
                    <InputError :message="errors.company_about" />
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>{{ t('app.admin.settings.contact') }}</CardTitle>
            </CardHeader>
            <CardContent class="grid gap-4 sm:grid-cols-3">
                <div class="grid gap-2">
                    <Label for="contact-phone">{{
                        t('app.admin.settings.fields.contact_phone')
                    }}</Label>
                    <Input
                        id="contact-phone"
                        name="contact_phone"
                        :default-value="values.contact_phone ?? ''"
                    />
                    <InputError :message="errors.contact_phone" />
                </div>
                <div class="grid gap-2">
                    <Label for="contact-whatsapp">{{
                        t('app.admin.settings.fields.contact_whatsapp')
                    }}</Label>
                    <Input
                        id="contact-whatsapp"
                        name="contact_whatsapp"
                        :default-value="values.contact_whatsapp ?? ''"
                    />
                    <InputError :message="errors.contact_whatsapp" />
                </div>
                <div class="grid gap-2">
                    <Label for="contact-email">{{
                        t('app.admin.settings.fields.contact_email')
                    }}</Label>
                    <Input
                        id="contact-email"
                        name="contact_email"
                        type="email"
                        :default-value="values.contact_email ?? ''"
                    />
                    <InputError :message="errors.contact_email" />
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>{{ t('app.admin.settings.address') }}</CardTitle>
            </CardHeader>
            <CardContent class="grid gap-4 sm:grid-cols-3">
                <div class="grid gap-2 sm:col-span-2">
                    <Label for="address-street">{{
                        t('app.admin.settings.fields.address_street')
                    }}</Label>
                    <Input
                        id="address-street"
                        name="address_street"
                        :default-value="values.address_street ?? ''"
                    />
                    <InputError :message="errors.address_street" />
                </div>
                <div class="grid gap-2">
                    <Label for="address-number">{{
                        t('app.admin.settings.fields.address_number')
                    }}</Label>
                    <Input
                        id="address-number"
                        name="address_number"
                        :default-value="values.address_number ?? ''"
                    />
                    <InputError :message="errors.address_number" />
                </div>
                <div class="grid gap-2">
                    <Label for="address-complement">{{
                        t('app.admin.settings.fields.address_complement')
                    }}</Label>
                    <Input
                        id="address-complement"
                        name="address_complement"
                        :default-value="values.address_complement ?? ''"
                    />
                    <InputError :message="errors.address_complement" />
                </div>
                <div class="grid gap-2">
                    <Label for="address-district">{{
                        t('app.admin.settings.fields.address_district')
                    }}</Label>
                    <Input
                        id="address-district"
                        name="address_district"
                        :default-value="values.address_district ?? ''"
                    />
                    <InputError :message="errors.address_district" />
                </div>
                <div class="grid gap-2">
                    <Label for="address-city">{{
                        t('app.admin.settings.fields.address_city')
                    }}</Label>
                    <Input
                        id="address-city"
                        name="address_city"
                        :default-value="values.address_city ?? ''"
                    />
                    <InputError :message="errors.address_city" />
                </div>
                <div class="grid gap-2">
                    <Label for="address-state">{{
                        t('app.admin.settings.fields.address_state')
                    }}</Label>
                    <Input
                        id="address-state"
                        name="address_state"
                        maxlength="2"
                        :default-value="values.address_state ?? ''"
                    />
                    <InputError :message="errors.address_state" />
                </div>
                <div class="grid gap-2">
                    <Label for="address-zip">{{
                        t('app.admin.settings.fields.address_zip')
                    }}</Label>
                    <Input
                        id="address-zip"
                        name="address_zip"
                        :default-value="values.address_zip ?? ''"
                    />
                    <InputError :message="errors.address_zip" />
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>{{ t('app.admin.settings.content') }}</CardTitle>
            </CardHeader>
            <CardContent class="grid gap-2">
                <Label for="content-terms">{{
                    t('app.admin.settings.fields.content_terms')
                }}</Label>
                <textarea
                    id="content-terms"
                    name="content_terms"
                    rows="10"
                    class="w-full rounded-md border border-input bg-transparent px-3 py-2 font-mono text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                    :value="values.content_terms ?? ''"
                ></textarea>
                <InputError :message="errors.content_terms" />
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>{{ t('app.admin.settings.appearance') }}</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
                <fieldset class="grid gap-3 sm:grid-cols-2">
                    <legend class="mb-2 text-sm font-medium">
                        {{ t('app.admin.settings.fields.site_default_theme') }}
                    </legend>
                    <label
                        v-for="theme in themes"
                        :key="theme.value"
                        class="flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-colors"
                        :class="
                            themeValue === theme.value
                                ? 'border-primary ring-2 ring-primary/30'
                                : 'border-border hover:border-primary/50'
                        "
                    >
                        <input
                            v-model="themeValue"
                            type="radio"
                            name="site_default_theme"
                            :value="theme.value"
                            class="sr-only"
                        />
                        <span
                            class="size-8 rounded-full border"
                            :class="
                                theme.value === 'terracotta'
                                    ? 'bg-[#9a3412]'
                                    : 'bg-[#57534e]'
                            "
                            aria-hidden="true"
                        ></span>
                        <span class="text-sm font-medium">{{
                            theme.label
                        }}</span>
                    </label>
                </fieldset>
                <p class="text-xs text-muted-foreground">
                    {{ t('app.admin.settings.theme_hint') }}
                </p>
                <InputError :message="errors.site_default_theme" />

                <div class="grid gap-2">
                    <Label for="site-logo">{{
                        t('app.admin.settings.fields.logo')
                    }}</Label>
                    <div class="flex items-center gap-4">
                        <img
                            v-if="logoUrl"
                            :src="logoUrl"
                            alt=""
                            class="h-12 w-auto rounded-md border bg-muted object-contain p-1"
                        />
                        <Input
                            id="site-logo"
                            name="logo"
                            type="file"
                            accept="image/*"
                            class="max-w-xs"
                        />
                    </div>
                    <InputError :message="errors.logo" />
                </div>
            </CardContent>
        </Card>

        <div class="flex justify-end">
            <Button type="submit" :disabled="processing">
                {{
                    processing
                        ? t('app.admin.common.saving')
                        : t('app.admin.common.save')
                }}
            </Button>
        </div>
    </Form>
</template>
