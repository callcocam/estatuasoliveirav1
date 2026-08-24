<script setup lang="ts">
import { Form, Head, setLayoutProps } from '@inertiajs/vue3';
import { computed, ref, watchEffect } from 'vue';
import InputError from '@/components/InputError.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import SiteInput from '@/components/site/SiteInput.vue';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import { useT } from '@/composables/useT';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { store } from '@/routes/two-factor/login';
import type { TwoFactorConfigContent } from '@/types';

defineOptions({ layout: AuthLayout });

const { t } = useT();

const showRecoveryInput = ref<boolean>(false);
const code = ref<string>('');

const authConfigContent = computed<TwoFactorConfigContent>(() => {
    if (showRecoveryInput.value) {
        return {
            title: 'app.auth.two_factor.recovery.title',
            description: 'app.auth.two_factor.recovery.description',
            buttonText: 'app.auth.two_factor.recovery.toggle',
        };
    }

    return {
        title: 'app.auth.two_factor.code.title',
        description: 'app.auth.two_factor.code.description',
        buttonText: 'app.auth.two_factor.code.toggle',
    };
});

watchEffect(() => {
    setLayoutProps({
        title: authConfigContent.value.title,
        description: authConfigContent.value.description,
    });
});

const toggleRecoveryMode = (clearErrors: () => void): void => {
    showRecoveryInput.value = !showRecoveryInput.value;
    clearErrors();
    code.value = '';
};
</script>

<template>
    <Head :title="t('app.auth.two_factor.head_title')" />

    <div class="space-y-6">
        <template v-if="!showRecoveryInput">
            <Form
                v-bind="store.form()"
                class="space-y-4"
                reset-on-error
                @error="code = ''"
                #default="{ errors, processing, clearErrors }"
            >
                <input type="hidden" name="code" :value="code" />
                <div
                    class="flex flex-col items-center justify-center space-y-3 text-center"
                >
                    <div class="flex w-full items-center justify-center">
                        <InputOTP
                            id="otp"
                            v-model="code"
                            :maxlength="6"
                            :disabled="processing"
                            autofocus
                        >
                            <InputOTPGroup>
                                <InputOTPSlot
                                    v-for="index in 6"
                                    :key="index"
                                    :index="index - 1"
                                />
                            </InputOTPGroup>
                        </InputOTP>
                    </div>
                    <InputError :message="errors.code" />
                </div>
                <SiteButton
                    type="submit"
                    class="w-full"
                    :disabled="processing"
                    >{{ t('app.auth.two_factor.continue') }}</SiteButton
                >
                <div class="text-center text-sm text-site-on-surface-variant">
                    <span>{{ t('app.auth.two_factor.or_you_can') }} </span>
                    <button
                        type="button"
                        class="text-site-primary underline decoration-site-outline-variant underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current!"
                        @click="() => toggleRecoveryMode(clearErrors)"
                    >
                        {{ t(authConfigContent.buttonText) }}
                    </button>
                </div>
            </Form>
        </template>

        <template v-else>
            <Form
                v-bind="store.form()"
                class="space-y-4"
                reset-on-error
                #default="{ errors, processing, clearErrors }"
            >
                <SiteInput
                    name="recovery_code"
                    type="text"
                    :placeholder="t('app.auth.two_factor.recovery.placeholder')"
                    :autofocus="showRecoveryInput"
                    required
                />
                <InputError :message="errors.recovery_code" />
                <SiteButton
                    type="submit"
                    class="w-full"
                    :disabled="processing"
                    >{{ t('app.auth.two_factor.continue') }}</SiteButton
                >

                <div class="text-center text-sm text-site-on-surface-variant">
                    <span>{{ t('app.auth.two_factor.or_you_can') }} </span>
                    <button
                        type="button"
                        class="text-site-primary underline decoration-site-outline-variant underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current!"
                        @click="() => toggleRecoveryMode(clearErrors)"
                    >
                        {{ t(authConfigContent.buttonText) }}
                    </button>
                </div>
            </Form>
        </template>
    </div>
</template>
