<script setup lang="ts">
import { Form, Head, setLayoutProps } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import SiteInput from '@/components/site/SiteInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useT } from '@/composables/useT';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { email } from '@/routes/password';

defineOptions({ layout: AuthLayout });

setLayoutProps({
    title: 'app.auth.forgot_password.title',
    description: 'app.auth.forgot_password.description',
});

defineProps<{
    status?: string;
}>();

const { t } = useT();
</script>

<template>
    <Head :title="t('app.auth.forgot_password.head_title')" />

    <div
        v-if="status"
        class="mb-4 text-center text-sm font-medium text-green-600"
    >
        {{ status }}
    </div>

    <div class="space-y-6">
        <Form v-bind="email.form()" v-slot="{ errors, processing }">
            <div class="grid gap-2">
                <Label for="email">{{
                    t('app.auth.fields.email_address')
                }}</Label>
                <SiteInput
                    id="email"
                    type="email"
                    name="email"
                    autocomplete="off"
                    autofocus
                    :placeholder="t('app.auth.fields.email_placeholder')"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="my-6 flex items-center justify-start">
                <SiteButton
                    class="w-full"
                    :disabled="processing"
                    data-test="email-password-reset-link-button"
                >
                    <Spinner v-if="processing" />
                    {{ t('app.auth.forgot_password.submit') }}
                </SiteButton>
            </div>
        </Form>

        <div class="space-x-1 text-center text-sm text-site-on-surface-variant">
            <span>{{ t('app.auth.forgot_password.or_return_to') }}</span>
            <TextLink
                :href="login()"
                class="text-site-primary decoration-site-outline-variant"
                >{{ t('app.auth.forgot_password.log_in') }}</TextLink
            >
        </div>
    </div>
</template>
