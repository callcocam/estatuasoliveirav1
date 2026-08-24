<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useT } from '@/composables/useT';
import AdminLayout from '@/layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

defineProps<{
    intro: string;
    sections: { title: string; body: string }[];
}>();

const { t } = useT();
</script>

<template>
    <Head :title="t('app.admin.help.title')" />

    <h1 class="text-2xl font-semibold">{{ t('app.admin.help.title') }}</h1>
    <p class="max-w-3xl text-muted-foreground">{{ intro }}</p>

    <div class="mt-6 space-y-4">
        <Card v-for="section in sections" :key="section.title">
            <CardHeader>
                <CardTitle>{{ section.title }}</CardTitle>
            </CardHeader>
            <CardContent>
                <p
                    class="max-w-3xl text-sm leading-relaxed whitespace-pre-line"
                >
                    {{ section.body }}
                </p>
            </CardContent>
        </Card>
    </div>
</template>
