<script setup lang="ts">
import { computed } from 'vue';
import SiteLayout from '@/layouts/SiteLayout.vue';

export interface LegalContent {
    title: string;
    intro: string;
    updated: string;
    sections: { title: string; body: string }[];
}

const props = defineProps<{
    metaTitle: string;
    content: string | null;
    legal: LegalContent;
}>();

/**
 * Blocos do texto personalizado separados por linha em branco.
 * Blocos de uma única linha iniciados por numeração ("1. …") viram títulos.
 */
const contentBlocks = computed(() =>
    (props.content ?? '')
        .split(/\n\s*\n/)
        .map((block) => block.trim())
        .filter((block) => block !== '')
        .map((block) => ({
            text: block,
            heading: !block.includes('\n') && /^\d+[.)]\s/.test(block),
        })),
);
</script>

<template>
    <SiteLayout :title="metaTitle">
        <div class="mx-auto max-w-3xl px-4 py-16 md:px-6 md:py-24">
            <h1 class="font-display text-4xl text-site-primary">
                {{ legal.title }}
            </h1>
            <p class="mt-3 text-sm text-site-on-surface-variant">
                {{ legal.updated }}
            </p>

            <div
                v-if="content"
                class="mt-10 leading-relaxed text-site-on-surface"
            >
                <template v-for="(block, index) in contentBlocks" :key="index">
                    <h2
                        v-if="block.heading"
                        class="mt-8 font-display text-xl text-site-primary"
                    >
                        {{ block.text }}
                    </h2>
                    <p v-else class="mt-3 whitespace-pre-line">
                        {{ block.text }}
                    </p>
                </template>
            </div>

            <div v-else class="mt-10 leading-relaxed text-site-on-surface">
                <p class="whitespace-pre-line">{{ legal.intro }}</p>
                <section
                    v-for="section in legal.sections"
                    :key="section.title"
                    class="mt-8"
                >
                    <h2 class="font-display text-xl text-site-primary">
                        {{ section.title }}
                    </h2>
                    <p class="mt-3 whitespace-pre-line">{{ section.body }}</p>
                </section>
            </div>
        </div>
    </SiteLayout>
</template>
