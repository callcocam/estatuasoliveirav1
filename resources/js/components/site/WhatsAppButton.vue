<script setup lang="ts">
import { computed } from 'vue';
import { useCompany } from '@/composables/useCompany';
import { useT } from '@/composables/useT';

/**
 * Botão de conversa no WhatsApp na cor verde da marca (tokens site-whatsapp).
 *
 * O número vem das configurações do tenant via useCompany() (prop `site`
 * compartilhada); quando não há número cadastrado, nada é renderizado.
 *
 * - `floating`: vira o botão fixo (FAB) na lateral inferior direita da tela.
 * - `message`: texto pré-preenchido da conversa (ex.: orçamento de produto).
 * - `label`: texto do botão inline (padrão: app.site.whatsapp.button).
 */
const props = defineProps<{
    floating?: boolean;
    message?: string;
    label?: string;
}>();

const { t } = useT();
const { whatsappUrl } = useCompany();

const href = computed<string | null>(() => {
    if (!whatsappUrl.value) {
        return null;
    }

    if (!props.message) {
        return whatsappUrl.value;
    }

    return `${whatsappUrl.value}?text=${encodeURIComponent(props.message)}`;
});
</script>

<template>
    <a
        v-if="href"
        :href="href"
        target="_blank"
        rel="noopener"
        :aria-label="floating ? t('app.site.whatsapp.aria_label') : undefined"
        :class="
            floating
                ? 'fixed right-4 bottom-4 z-50 flex size-14 items-center justify-center rounded-full bg-site-whatsapp text-site-on-whatsapp shadow-lg transition-colors hover:bg-site-whatsapp-hover md:right-6 md:bottom-6'
                : 'inline-flex items-center gap-2 rounded-site bg-site-whatsapp px-6 py-2.5 text-sm font-medium text-site-on-whatsapp transition-colors hover:bg-site-whatsapp-hover'
        "
    >
        <svg
            viewBox="0 0 24 24"
            fill="currentColor"
            aria-hidden="true"
            :class="floating ? 'size-7' : 'size-4'"
        >
            <path
                d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38a9.87 9.87 0 0 0 4.74 1.21c5.46 0 9.9-4.45 9.9-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2m0 1.67c2.2 0 4.26.86 5.82 2.42a8.2 8.2 0 0 1 2.41 5.83c0 4.54-3.7 8.23-8.24 8.23-1.48 0-2.93-.39-4.19-1.15l-.3-.17-3.12.82.83-3.04-.2-.31a8.2 8.2 0 0 1-1.26-4.38c0-4.54 3.7-8.25 8.25-8.25M8.53 7.33c-.16 0-.43.06-.66.31-.22.25-.87.86-.87 2.07 0 1.22.89 2.39 1 2.56.14.17 1.76 2.67 4.25 3.73.59.27 1.05.42 1.41.53.59.19 1.13.16 1.56.1.48-.07 1.46-.6 1.67-1.18.21-.58.21-1.07.15-1.18-.07-.1-.23-.16-.48-.27-.25-.14-1.47-.74-1.69-.82-.23-.08-.37-.12-.56.12-.16.25-.64.81-.78.97-.15.17-.29.19-.53.07-.26-.13-1.06-.39-2-1.23-.74-.66-1.23-1.47-1.38-1.72-.12-.24-.01-.39.11-.5.11-.11.27-.29.37-.44.13-.14.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.11-.56-1.35-.77-1.84-.2-.48-.4-.42-.56-.43-.14 0-.3-.01-.47-.01"
            />
        </svg>
        <span v-if="!floating">{{
            label ?? t('app.site.whatsapp.button')
        }}</span>
    </a>
</template>
