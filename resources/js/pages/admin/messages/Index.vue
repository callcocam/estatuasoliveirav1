<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ColumnActions from '@/components/admin/ColumnActions.vue';
import ListFiltersBar from '@/components/admin/ListFiltersBar.vue';
import ListPage from '@/components/admin/ListPage.vue';
import { Badge } from '@/components/ui/badge';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useDeferredPaginator } from '@/composables/useDeferredPaginator';
import { useT } from '@/composables/useT';
import AdminLayout from '@/layouts/AdminLayout.vue';
import {
    destroy as messageDestroy,
    index as messagesIndex,
    restore as messageRestore,
    show as messageShow,
} from '@/routes/admin/messages';
import type { ContactMessageRow, Paginated, ResourceAbilities } from '@/types/admin';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    messages?: Paginated<ContactMessageRow>;
    filters: {
        search: string;
        read: string;
        trashed: string;
        per_page: string;
    };
    can: ResourceAbilities;
}>();

const { t } = useT();

const { isLoading, isEmpty, rows, links } =
    useDeferredPaginator<ContactMessageRow>(() => props.messages);

const readOptions = [
    { value: 'all', label: t('app.admin.common.filter_all') },
    { value: 'unread', label: t('app.admin.messages.unread') },
    { value: 'read', label: t('app.admin.messages.read') },
];

function formatDate(value: string | null): string {
    return value ? new Date(value).toLocaleDateString('pt-BR') : '';
}
</script>

<template>
    <Head :title="t('app.admin.messages.title')" />

    <ListPage
        :title="t('app.admin.messages.title')"
        :loading="isLoading"
        :empty="isEmpty"
        :columns="5"
        :links="links"
    >
        <template #filters>
            <ListFiltersBar
                :index-url="messagesIndex().url"
                :filters="{
                    search: filters.search,
                    read: filters.read || 'all',
                    trashed: filters.trashed,
                    per_page: filters.per_page,
                }"
                :search-placeholder="t('app.admin.messages.search_placeholder')"
            >
                <template #default="{ values, set }">
                    <Select
                        :model-value="values.read"
                        @update:model-value="set('read', String($event ?? 'all'))"
                    >
                        <SelectTrigger class="w-44" :aria-label="t('app.admin.common.status')">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="option in readOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </template>
            </ListFiltersBar>
        </template>

        <template #head>
            <th class="px-4 py-3 font-medium">{{ t('app.admin.messages.from') }}</th>
            <th class="hidden px-4 py-3 font-medium md:table-cell">
                {{ t('app.admin.messages.subject') }}
            </th>
            <th class="hidden px-4 py-3 font-medium md:table-cell">
                {{ t('app.admin.messages.received_at') }}
            </th>
            <th class="px-4 py-3 font-medium">{{ t('app.admin.common.status') }}</th>
            <th class="px-4 py-3 text-right font-medium">
                {{ t('app.admin.common.actions') }}
            </th>
        </template>

        <template #body>
            <tr
                v-for="message in rows"
                :key="message.id"
                class="border-b border-border last:border-b-0"
                :class="{ 'opacity-60': message.deleted, 'font-medium': !message.read }"
            >
                <td class="px-4 py-3">
                    <p class="text-foreground">{{ message.name }}</p>
                    <p class="text-xs font-normal text-muted-foreground">
                        {{ message.email }}
                    </p>
                </td>
                <td class="hidden px-4 py-3 md:table-cell">
                    {{ message.subject ?? t('app.admin.common.none') }}
                </td>
                <td class="hidden px-4 py-3 md:table-cell">
                    {{ formatDate(message.createdAt) }}
                </td>
                <td class="px-4 py-3">
                    <div class="flex flex-wrap items-center gap-1">
                        <Badge :variant="message.read ? 'secondary' : 'default'">
                            {{
                                message.read
                                    ? t('app.admin.messages.read')
                                    : t('app.admin.messages.unread')
                            }}
                        </Badge>
                        <Badge v-if="message.deleted" variant="destructive">
                            {{ t('app.admin.common.deleted_badge') }}
                        </Badge>
                    </div>
                </td>
                <td class="px-4 py-3">
                    <ColumnActions
                        :trashed="message.deleted"
                        :edit-href="messageShow(message.id).url"
                        :delete-href="messageDestroy(message.id).url"
                        :restore-href="messageRestore(message.id).url"
                        :can-update="can.update"
                        :can-delete="can.delete"
                    />
                </td>
            </tr>
        </template>
    </ListPage>
</template>
