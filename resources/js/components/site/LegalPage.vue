<script setup lang="ts">
import SiteLayout from '@/layouts/SiteLayout.vue';

export interface LegalContent {
    title: string;
    intro: string;
    updated: string;
    sections: { title: string; body: string }[];
}

defineProps<{
    metaTitle: string;
    content: string | null;
    legal: LegalContent;
}>();
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
                <p class="whitespace-pre-line">{{ content }}</p>
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
