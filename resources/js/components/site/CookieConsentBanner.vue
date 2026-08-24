<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { useT } from '@/composables/useT';
import { privacy } from '@/routes';

const STORAGE_KEY = 'cookie_consent';

const { t } = useT();
const visible = ref(false);

onMounted(() => {
    try {
        visible.value = localStorage.getItem(STORAGE_KEY) === null;
    } catch {
        visible.value = false;
    }
});

function choose(value: 'accepted' | 'essential_only') {
    try {
        localStorage.setItem(
            STORAGE_KEY,
            JSON.stringify({ value, date: new Date().toISOString() }),
        );
    } catch {
        // Sem armazenamento disponível: apenas fecha o aviso nesta visita.
    }

    visible.value = false;
}
</script>

<template>
    <div
        v-if="visible"
        role="dialog"
        :aria-label="t('app.site.cookies.title')"
        class="fixed inset-x-0 bottom-0 z-50 border-t border-site-outline-variant bg-site-surface-container-low p-4 shadow-lg md:p-6"
    >
        <div
            class="mx-auto flex max-w-6xl flex-col gap-4 md:flex-row md:items-center md:justify-between"
        >
            <div class="max-w-2xl">
                <p class="font-semibold text-site-on-surface">
                    {{ t('app.site.cookies.title') }}
                </p>
                <p
                    class="mt-1 text-sm leading-relaxed text-site-on-surface-variant"
                >
                    {{ t('app.site.cookies.text') }}
                    <Link
                        :href="privacy()"
                        class="underline transition-colors hover:text-site-primary"
                    >
                        {{ t('app.site.cookies.privacy_link') }}
                    </Link>
                </p>
            </div>
            <div class="flex shrink-0 flex-wrap gap-3">
                <button
                    type="button"
                    class="rounded-full border border-site-outline-variant px-5 py-2 text-sm text-site-on-surface transition-colors hover:bg-site-surface"
                    @click="choose('essential_only')"
                >
                    {{ t('app.site.cookies.essential_only') }}
                </button>
                <button
                    type="button"
                    class="rounded-full bg-site-primary px-5 py-2 text-sm font-medium text-site-on-primary transition-opacity hover:opacity-90"
                    @click="choose('accepted')"
                >
                    {{ t('app.site.cookies.accept') }}
                </button>
            </div>
        </div>
    </div>
</template>
