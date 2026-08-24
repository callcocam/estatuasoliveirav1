<script setup lang="ts">
import { Form, Head, setLayoutProps } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import SiteInput from '@/components/site/SiteInput.vue';
import SitePasswordInput from '@/components/site/SitePasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useT } from '@/composables/useT';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';

defineProps<{
    passwordRules: string;
}>();

defineOptions({ layout: AuthLayout });

setLayoutProps({
    title: 'app.auth.register.title',
    description: 'app.auth.register.description',
});

const { t } = useT();
</script>

<template>
    <Head :title="t('app.auth.register.head_title')" />

    <Form
        v-bind="store.form()"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="name">{{ t('app.auth.fields.name') }}</Label>
                <SiteInput
                    id="name"
                    type="text"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="name"
                    name="name"
                    :placeholder="t('app.auth.fields.name_placeholder')"
                />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email">{{
                    t('app.auth.fields.email_address')
                }}</Label>
                <SiteInput
                    id="email"
                    type="email"
                    required
                    :tabindex="2"
                    autocomplete="email"
                    name="email"
                    :placeholder="t('app.auth.fields.email_placeholder')"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="password">{{
                    t('app.auth.fields.password')
                }}</Label>
                <SitePasswordInput
                    id="password"
                    required
                    :tabindex="3"
                    autocomplete="new-password"
                    name="password"
                    :placeholder="t('app.auth.fields.password_placeholder')"
                    :passwordrules="passwordRules"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">{{
                    t('app.auth.fields.password_confirmation')
                }}</Label>
                <SitePasswordInput
                    id="password_confirmation"
                    required
                    :tabindex="4"
                    autocomplete="new-password"
                    name="password_confirmation"
                    :placeholder="
                        t('app.auth.fields.password_confirmation_placeholder')
                    "
                    :passwordrules="passwordRules"
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <SiteButton
                type="submit"
                class="mt-2 w-full"
                tabindex="5"
                :disabled="processing"
                data-test="register-user-button"
            >
                <Spinner v-if="processing" />
                {{ t('app.auth.register.submit') }}
            </SiteButton>
        </div>

        <div class="text-center text-sm text-site-on-surface-variant">
            {{ t('app.auth.register.already_registered') }}
            <TextLink
                :href="login()"
                class="text-site-primary underline decoration-site-outline-variant underline-offset-4"
                :tabindex="6"
                data-test="login-link"
            >
                {{ t('app.auth.register.log_in') }}
            </TextLink>
        </div>
    </Form>
</template>
