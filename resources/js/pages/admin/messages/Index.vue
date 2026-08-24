<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { RotateCcw } from '@lucide/vue';
import { ref } from 'vue';
import AdminPagination from '@/components/admin/AdminPagination.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useT } from '@/composables/useT';
import AdminLayout from '@/layouts/AdminLayout.vue';
import {
    index as messagesIndex,
    restore as messageRestore,
    show as messageShow,
} from '@/routes/admin/messages';
import type { Paginated } from '@/types/admin';

defineOptions({ layout: AdminLayout });

type MessageRow = {
    id: string;
    name: string;
    email: string;
    subject: string | null;
    read: boolean;
    createdAt: string | null;
    deleted: boolean;
};

const props = defineProps<{
    messages: Paginated<MessageRow>;
    filters: { filter: string | null; search: string | null };
}>();

const { t } = useT();

const filter = ref(props.filters.filter ?? 'all');
const search = ref(props.filters.search ?? '');

function applyFilters() {
    router.get(
        messagesIndex().url,
        {
            filter: filter.value !== 'all' ? filter.value : undefined,
            search: search.value || undefined,
        },
        { preserveState: true, preserveScroll: true },
    );
}

function formatDate(value: string | null): string {
    return value ? new Date(value).toLocaleDateString('pt-BR') : '';
}
</script>

<template>
    <Head :title="t('app.admin.messages.title')" />

    <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold">
            {{ t('app.admin.messages.title') }}
        </h1>
        <div class="flex items-center gap-3">
            <Input
                v-model="search"
                type="search"
                :placeholder="t('app.admin.messages.search_placeholder')"
                class="max-w-xs"
                @keydown.enter="applyFilters"
            />
            <Select v-model="filter" @update:model-value="applyFilters">
                <SelectTrigger class="w-44">
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">{{
                        t('app.admin.common.filter_all')
                    }}</SelectItem>
                    <SelectItem value="unread">{{
                        t('app.admin.messages.unread')
                    }}</SelectItem>
                    <SelectItem value="read">{{
                        t('app.admin.messages.read')
                    }}</SelectItem>
                    <SelectItem value="trashed">{{
                        t('app.admin.common.filter_trashed')
                    }}</SelectItem>
                </SelectContent>
            </Select>
        </div>
    </div>

    <div class="overflow-x-auto rounded-lg border">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b bg-muted/50 text-left">
                    <th class="px-4 py-3 font-medium">
                        {{ t('app.admin.messages.from') }}
                    </th>
                    <th class="px-4 py-3 font-medium">
                        {{ t('app.admin.messages.subject') }}
                    </th>
                    <th class="px-4 py-3 font-medium">
                        {{ t('app.admin.messages.received_at') }}
                    </th>
                    <th class="px-4 py-3 font-medium">
                        {{ t('app.admin.common.status') }}
                    </th>
                    <th class="px-4 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="messages.data.length === 0">
                    <td
                        colspan="5"
                        class="px-4 py-8 text-center text-muted-foreground"
                    >
                        {{ t('app.admin.common.empty') }}
                    </td>
                </tr>
                <tr
                    v-for="message in messages.data"
                    :key="message.id"
                    class="border-b last:border-b-0"
                    :class="{ 'font-medium': !message.read }"
                >
                    <td class="px-4 py-3">
                        <p>{{ message.name }}</p>
                        <p class="text-xs font-normal text-muted-foreground">
                            {{ message.email }}
                        </p>
                    </td>
                    <td class="px-4 py-3">
                        {{ message.subject ?? t('app.admin.common.none') }}
                    </td>
                    <td class="px-4 py-3">
                        {{ formatDate(message.createdAt) }}
                    </td>
                    <td class="px-4 py-3">
                        <Badge
                            :variant="message.read ? 'secondary' : 'default'"
                        >
                            {{
                                message.read
                                    ? t('app.admin.messages.read')
                                    : t('app.admin.messages.unread')
                            }}
                        </Badge>
                        <Badge
                            v-if="message.deleted"
                            variant="destructive"
                            class="ml-1"
                        >
                            {{ t('app.admin.common.deleted_badge') }}
                        </Badge>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <Button
                            v-if="message.deleted"
                            type="button"
                            variant="outline"
                            size="sm"
                            @click="
                                router.post(
                                    messageRestore(message.id).url,
                                    {},
                                    { preserveScroll: true },
                                )
                            "
                        >
                            <RotateCcw />
                            {{ t('app.admin.common.restore') }}
                        </Button>
                        <Button v-else variant="outline" size="sm" as-child>
                            <Link :href="messageShow(message.id).url">{{
                                t('app.admin.common.edit')
                            }}</Link>
                        </Button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <AdminPagination :links="messages.links" />
</template>
