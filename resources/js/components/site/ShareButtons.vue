<script setup lang="ts">
import { Check, Link } from '@lucide/vue';
import { computed, ref } from 'vue';
import { useCompany } from '@/composables/useCompany';
import { useT } from '@/composables/useT';

/**
 * Botões de compartilhamento sem dependências externas, sempre visíveis:
 * WhatsApp, Facebook, Instagram e copiar link.
 *
 * WhatsApp/Facebook são links diretos (só URL, zero JS de terceiros). O
 * Instagram não expõe URL de compartilhamento web com link pré-preenchido,
 * então o botão copia o link e abre o Instagram em nova aba. Copiar usa
 * navigator.clipboard com feedback "Link copiado!".
 */
const props = defineProps<{
    url: string;
    title: string;
}>();

const { t } = useT();
const { company } = useCompany();

const copied = ref(false);

/**
 * Mensagem de compartilhamento com a identidade da empresa (nome/tagline e
 * telefone vêm dos settings via prop `site` — useCompany), não só o link.
 */
const shareMessage = computed(() => {
    const lines = [
        t('app.site.share.message', {
            title: props.title,
            company: company.value.name,
            tagline: t('app.site.nav.tagline'),
        }),
        props.url,
    ];

    const phone = company.value.whatsapp ?? company.value.phone;

    if (phone) {
        lines.push(t('app.site.share.contact', { phone }));
    }

    return lines.join('\n');
});

const whatsappHref = computed(
    () => `https://wa.me/?text=${encodeURIComponent(shareMessage.value)}`,
);

const facebookHref = computed(
    () =>
        `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(props.url)}`,
);

async function copyLink(): Promise<void> {
    await navigator.clipboard.writeText(props.url);
    copied.value = true;
    window.setTimeout(() => (copied.value = false), 2000);
}

async function shareOnInstagram(): Promise<void> {
    await copyLink();
    window.open('https://www.instagram.com/', '_blank', 'noopener');
}

/**
 * Usa currentColor para herdar a cor do contexto: funciona tanto na página de
 * produto (superfície clara) quanto no lightbox da galeria (superfície inversa).
 */
const iconButtonClass =
    'inline-flex size-9 items-center justify-center rounded-full border border-current/30 text-current transition-opacity hover:opacity-70';
</script>

<template>
    <div class="flex items-center gap-2">
        <a
            :href="whatsappHref"
            target="_blank"
            rel="noopener"
            :aria-label="t('app.site.share.whatsapp')"
            :class="iconButtonClass"
        >
            <svg
                viewBox="0 0 24 24"
                fill="currentColor"
                aria-hidden="true"
                class="size-4"
            >
                <path
                    d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38a9.87 9.87 0 0 0 4.74 1.21c5.46 0 9.9-4.45 9.9-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2m0 1.67c2.2 0 4.26.86 5.82 2.42a8.2 8.2 0 0 1 2.41 5.83c0 4.54-3.7 8.23-8.24 8.23-1.48 0-2.93-.39-4.19-1.15l-.3-.17-3.12.82.83-3.04-.2-.31a8.2 8.2 0 0 1-1.26-4.38c0-4.54 3.7-8.25 8.25-8.25M8.53 7.33c-.16 0-.43.06-.66.31-.22.25-.87.86-.87 2.07 0 1.22.89 2.39 1 2.56.14.17 1.76 2.67 4.25 3.73.59.27 1.05.42 1.41.53.59.19 1.13.16 1.56.1.48-.07 1.46-.6 1.67-1.18.21-.58.21-1.07.15-1.18-.07-.1-.23-.16-.48-.27-.25-.14-1.47-.74-1.69-.82-.23-.08-.37-.12-.56.12-.16.25-.64.81-.78.97-.15.17-.29.19-.53.07-.26-.13-1.06-.39-2-1.23-.74-.66-1.23-1.47-1.38-1.72-.12-.24-.01-.39.11-.5.11-.11.27-.29.37-.44.13-.14.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.11-.56-1.35-.77-1.84-.2-.48-.4-.42-.56-.43-.14 0-.3-.01-.47-.01"
                />
            </svg>
        </a>
        <a
            :href="facebookHref"
            target="_blank"
            rel="noopener"
            :aria-label="t('app.site.share.facebook')"
            :class="iconButtonClass"
        >
            <svg
                viewBox="0 0 24 24"
                fill="currentColor"
                aria-hidden="true"
                class="size-4"
            >
                <path
                    d="M13.5 21.9v-8h2.7l.4-3.1h-3.1V8.8c0-.9.25-1.5 1.54-1.5h1.66V4.5c-.29-.04-1.27-.12-2.42-.12-2.4 0-4.04 1.46-4.04 4.15v2.32H7.5v3.1h2.74v8a10 10 0 1 1 3.26 0"
                />
            </svg>
        </a>
        <button
            type="button"
            :aria-label="t('app.site.share.instagram')"
            :class="iconButtonClass"
            @click="shareOnInstagram"
        >
            <svg
                viewBox="0 0 24 24"
                fill="currentColor"
                aria-hidden="true"
                class="size-4"
            >
                <path
                    d="M12 2c2.72 0 3.06.01 4.12.06 1.07.05 1.8.22 2.43.47.66.25 1.21.6 1.77 1.15.55.56.9 1.11 1.15 1.77.25.64.42 1.36.47 2.43.05 1.06.06 1.4.06 4.12s-.01 3.06-.06 4.12c-.05 1.07-.22 1.8-.47 2.43a4.9 4.9 0 0 1-1.15 1.77c-.56.55-1.11.9-1.77 1.15-.64.25-1.36.42-2.43.47-1.06.05-1.4.06-4.12.06s-3.06-.01-4.12-.06c-1.07-.05-1.8-.22-2.43-.47a4.9 4.9 0 0 1-1.77-1.15 4.9 4.9 0 0 1-1.15-1.77c-.25-.64-.42-1.36-.47-2.43C2.01 15.06 2 14.72 2 12s.01-3.06.06-4.12c.05-1.07.22-1.8.47-2.43.25-.66.6-1.21 1.15-1.77a4.9 4.9 0 0 1 1.77-1.15c.64-.25 1.36-.42 2.43-.47C8.94 2.01 9.28 2 12 2m0 1.8c-2.67 0-2.99.01-4.04.06-.98.04-1.5.2-1.86.34-.46.18-.8.4-1.15.75-.35.35-.57.69-.75 1.15-.14.35-.3.88-.34 1.86-.05 1.05-.06 1.37-.06 4.04s.01 2.99.06 4.04c.04.98.2 1.5.34 1.86.18.46.4.8.75 1.15.35.35.69.57 1.15.75.35.14.88.3 1.86.34 1.05.05 1.37.06 4.04.06s2.99-.01 4.04-.06c.98-.04 1.5-.2 1.86-.34.46-.18.8-.4 1.15-.75.35-.35.57-.69.75-1.15.14-.35.3-.88.34-1.86.05-1.05.06-1.37.06-4.04s-.01-2.99-.06-4.04c-.04-.98-.2-1.5-.34-1.86a3.1 3.1 0 0 0-.75-1.15 3.1 3.1 0 0 0-1.15-.75c-.35-.14-.88-.3-1.86-.34-1.05-.05-1.37-.06-4.04-.06M12 6.87a5.13 5.13 0 1 1 0 10.26 5.13 5.13 0 0 1 0-10.26m0 8.46a3.33 3.33 0 1 0 0-6.66 3.33 3.33 0 0 0 0 6.66m6.54-8.67a1.2 1.2 0 1 1-2.4 0 1.2 1.2 0 0 1 2.4 0"
                />
            </svg>
        </button>
        <button
            type="button"
            :aria-label="t('app.site.share.copy')"
            :class="iconButtonClass"
            @click="copyLink"
        >
            <Check v-if="copied" class="size-4" aria-hidden="true" />
            <Link v-else class="size-4" aria-hidden="true" />
        </button>
        <span v-if="copied" role="status" class="text-sm text-current/80">
            {{ t('app.site.share.copied') }}
        </span>
    </div>
</template>
