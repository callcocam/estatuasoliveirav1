<script setup lang="ts">
import type { LegalContent } from '@/components/site/LegalPage.vue';
import LegalPage from '@/components/site/LegalPage.vue';
import { useT } from '@/composables/useT';

defineProps<{
    content: string | null;
    legal: LegalContent;
}>();

const { t } = useT();
</script>

<template>
    <LegalPage
        :meta-title="t('app.site.meta.privacy_title')"
        :content="content"
        :legal="legal"
    />
</template>
