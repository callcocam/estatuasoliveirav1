<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useT } from '@/composables/useT';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { show as messageShow } from '@/routes/admin/messages';
import { show as quoteShow } from '@/routes/admin/quotes';

defineOptions({ layout: AdminLayout });

type Props = {
    stats: {
        productsPublished: number;
        productsDraft: number;
        categories: number;
        quotesPending: number;
        messagesUnread: number;
    };
    latestMessages: {
        id: string;
        name: string;
        subject: string | null;
        read: boolean;
        createdAt: string | null;
    }[];
    latestQuotes: {
        id: string;
        userName: string | null;
        status: string;
        statusLabel: string;
        total: string;
        createdAt: string | null;
    }[];
};

defineProps<Props>();

const { t } = useT();
</script>

<template>
    <Head :title="t('app.admin.dashboard.title')" />

    <h1 class="text-2xl font-semibold">{{ t('app.admin.dashboard.title') }}</h1>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
        <Card>
            <CardHeader class="pb-2">
                <CardTitle class="text-sm font-medium text-muted-foreground">
                    {{ t('app.admin.dashboard.products_published') }}
                </CardTitle>
            </CardHeader>
            <CardContent class="text-3xl font-semibold">{{
                stats.productsPublished
            }}</CardContent>
        </Card>
        <Card>
            <CardHeader class="pb-2">
                <CardTitle class="text-sm font-medium text-muted-foreground">
                    {{ t('app.admin.dashboard.products_draft') }}
                </CardTitle>
            </CardHeader>
            <CardContent class="text-3xl font-semibold">{{
                stats.productsDraft
            }}</CardContent>
        </Card>
        <Card>
            <CardHeader class="pb-2">
                <CardTitle class="text-sm font-medium text-muted-foreground">
                    {{ t('app.admin.dashboard.categories') }}
                </CardTitle>
            </CardHeader>
            <CardContent class="text-3xl font-semibold">{{
                stats.categories
            }}</CardContent>
        </Card>
        <Card>
            <CardHeader class="pb-2">
                <CardTitle class="text-sm font-medium text-muted-foreground">
                    {{ t('app.admin.dashboard.quotes_pending') }}
                </CardTitle>
            </CardHeader>
            <CardContent class="text-3xl font-semibold">{{
                stats.quotesPending
            }}</CardContent>
        </Card>
        <Card>
            <CardHeader class="pb-2">
                <CardTitle class="text-sm font-medium text-muted-foreground">
                    {{ t('app.admin.dashboard.messages_unread') }}
                </CardTitle>
            </CardHeader>
            <CardContent class="text-3xl font-semibold">{{
                stats.messagesUnread
            }}</CardContent>
        </Card>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <Card>
            <CardHeader>
                <CardTitle>{{
                    t('app.admin.dashboard.latest_messages')
                }}</CardTitle>
            </CardHeader>
            <CardContent>
                <p
                    v-if="latestMessages.length === 0"
                    class="text-sm text-muted-foreground"
                >
                    {{ t('app.admin.dashboard.no_messages') }}
                </p>
                <ul v-else class="divide-y">
                    <li v-for="message in latestMessages" :key="message.id">
                        <Link
                            :href="messageShow(message.id).url"
                            class="flex items-center justify-between gap-4 py-3 transition-colors hover:text-primary"
                        >
                            <span class="min-w-0">
                                <span class="block truncate font-medium">{{
                                    message.name
                                }}</span>
                                <span
                                    class="block truncate text-sm text-muted-foreground"
                                >
                                    {{
                                        message.subject ??
                                        t('app.admin.common.none')
                                    }}
                                </span>
                            </span>
                            <Badge v-if="!message.read" variant="default">
                                {{ t('app.admin.messages.unread') }}
                            </Badge>
                        </Link>
                    </li>
                </ul>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>{{
                    t('app.admin.dashboard.latest_quotes')
                }}</CardTitle>
            </CardHeader>
            <CardContent>
                <p
                    v-if="latestQuotes.length === 0"
                    class="text-sm text-muted-foreground"
                >
                    {{ t('app.admin.dashboard.no_quotes') }}
                </p>
                <ul v-else class="divide-y">
                    <li v-for="quote in latestQuotes" :key="quote.id">
                        <Link
                            :href="quoteShow(quote.id).url"
                            class="flex items-center justify-between gap-4 py-3 transition-colors hover:text-primary"
                        >
                            <span class="min-w-0">
                                <span class="block truncate font-medium">
                                    {{
                                        quote.userName ??
                                        t('app.admin.dashboard.visitor')
                                    }}
                                </span>
                                <span
                                    class="block text-sm text-muted-foreground"
                                >
                                    R$ {{ quote.total }}
                                </span>
                            </span>
                            <Badge variant="secondary">{{
                                quote.statusLabel
                            }}</Badge>
                        </Link>
                    </li>
                </ul>
            </CardContent>
        </Card>
    </div>
</template>
