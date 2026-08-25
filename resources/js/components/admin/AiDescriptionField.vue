<script setup lang="ts">
import { useHttp } from '@inertiajs/vue3';
import { Sparkles } from '@lucide/vue';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useT } from '@/composables/useT';

type AiPayload = Record<string, string | null>;

/**
 * Campo de descrição com geração por IA. O `payload` pode ser um objeto
 * pronto ou uma função que recebe o FormData do <form> mais próximo do
 * botão (útil quando os demais inputs do form são não-controlados).
 */
const props = withDefaults(
    defineProps<{
        modelValue: string;
        endpoint: string;
        payload: AiPayload | ((form: FormData) => AiPayload);
        error?: string;
        id?: string;
        name?: string;
        label?: string;
    }>(),
    {
        error: undefined,
        id: 'description',
        name: 'description',
        label: undefined,
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const { t } = useT();

const aiError = ref<string | null>(null);

const aiHttp = useHttp<AiPayload>({});

function generateDescription(event: MouseEvent): void {
    if (
        props.modelValue.trim() !== '' &&
        !window.confirm(t('app.admin.products.ai.confirm_overwrite'))
    ) {
        return;
    }

    const formElement = (event.currentTarget as HTMLElement).closest('form');
    const data = formElement ? new FormData(formElement) : new FormData();
    const payload =
        typeof props.payload === 'function'
            ? props.payload(data)
            : props.payload;

    aiError.value = null;

    aiHttp
        .transform(() => payload)
        .post(props.endpoint, {
            onSuccess: (response) => {
                emit(
                    'update:modelValue',
                    (response as { description?: string }).description ?? '',
                );
            },
            onError: () => {
                const errors = aiHttp.errors as Record<
                    string,
                    string | undefined
                >;

                aiError.value =
                    Object.values(errors).find(
                        (message) => typeof message === 'string',
                    ) ?? t('app.admin.products.ai.failed');
            },
        });
}
</script>

<template>
    <div class="grid gap-2">
        <div class="flex items-center justify-between gap-2">
            <Label :for="id">{{ label }}</Label>
            <Button
                type="button"
                variant="outline"
                size="sm"
                :disabled="aiHttp.processing"
                @click="generateDescription"
            >
                <Spinner v-if="aiHttp.processing" />
                <Sparkles v-else class="size-4" />
                {{
                    aiHttp.processing
                        ? t('app.admin.products.ai.generating')
                        : t('app.admin.products.ai.generate')
                }}
            </Button>
        </div>
        <textarea
            :id="id"
            :name="name"
            rows="6"
            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
            :value="modelValue"
            @input="
                emit(
                    'update:modelValue',
                    ($event.target as HTMLTextAreaElement).value,
                )
            "
        ></textarea>
        <InputError :message="aiError ?? error" />
    </div>
</template>
