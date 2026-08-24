<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useT } from '@/composables/useT';

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

defineProps<{
    links: PaginationLink[];
}>();

const { t } = useT();

function label(raw: string): string {
    if (raw.includes('Previous') || raw.includes('&laquo;')) {
        return t('app.admin.common.previous');
    }

    if (raw.includes('Next') || raw.includes('&raquo;')) {
        return t('app.admin.common.next');
    }

    return raw;
}
</script>

<template>
    <nav v-if="links.length > 3" class="flex flex-wrap items-center gap-1">
        <template v-for="(link, index) in links" :key="index">
            <Link
                v-if="link.url"
                :href="link.url"
                preserve-scroll
                class="rounded-md border px-3 py-1.5 text-sm transition-colors hover:bg-accent"
                :class="
                    link.active
                        ? 'border-primary bg-primary text-primary-foreground hover:bg-primary/90'
                        : 'border-border'
                "
            >
                {{ label(link.label) }}
            </Link>
            <span
                v-else
                class="rounded-md border border-transparent px-3 py-1.5 text-sm text-muted-foreground"
            >
                {{ label(link.label) }}
            </span>
        </template>
    </nav>
</template>
