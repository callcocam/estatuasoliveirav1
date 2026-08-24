<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDeleteDialog from '@/components/admin/ConfirmDeleteDialog.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useT } from '@/composables/useT';
import AdminLayout from '@/layouts/AdminLayout.vue';
import {
    destroy as messageDestroy,
    index as messagesIndex,
    read as messageRead,
} from '@/routes/admin/messages';

defineOptions({ layout: AdminLayout });

type MessageDetail = {
    id: string;
    name: string;
    email: string;
    phone: string | null;
    subject: string | null;
    message: string;
    read: boolean;
    createdAt: string | null;
};

const props = defineProps<{
    message: MessageDetail;
}>();

const { t } = useT();

const confirmingDelete = ref(false);

function toggleRead() {
    router.patch(
        messageRead(props.message.id).url,
        {},
        { preserveScroll: true },
    );
}

function confirmDelete() {
    router.delete(messageDestroy(props.message.id).url);
}

function formatDate(value: string | null): string {
    return value ? new Date(value).toLocaleString('pt-BR') : '';
}
</script>

<template>
    <Head :title="t('app.admin.messages.detail')" />

    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold">
            {{ t('app.admin.messages.detail') }}
        </h1>
        <div class="flex items-center gap-2">
            <Button variant="outline" type="button" @click="toggleRead">
                {{
                    message.read
                        ? t('app.admin.messages.mark_unread')
                        : t('app.admin.messages.mark_read')
                }}
            </Button>
            <Button
                variant="destructive"
                type="button"
                @click="confirmingDelete = true"
            >
                {{ t('app.admin.common.delete') }}
            </Button>
            <Button variant="outline" as-child>
                <Link :href="messagesIndex().url">{{
                    t('app.admin.common.back')
                }}</Link>
            </Button>
        </div>
    </div>

    <Card>
        <CardHeader>
            <CardTitle>{{
                message.subject ?? t('app.admin.common.none')
            }}</CardTitle>
        </CardHeader>
        <CardContent class="space-y-4 text-sm">
            <dl class="grid gap-2 sm:grid-cols-2">
                <div>
                    <dt class="text-muted-foreground">
                        {{ t('app.admin.messages.from') }}
                    </dt>
                    <dd class="font-medium">{{ message.name }}</dd>
                    <dd>
                        <a
                            :href="`mailto:${message.email}`"
                            class="text-primary hover:underline"
                        >
                            {{ message.email }}
                        </a>
                    </dd>
                    <dd v-if="message.phone">{{ message.phone }}</dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">
                        {{ t('app.admin.messages.received_at') }}
                    </dt>
                    <dd>{{ formatDate(message.createdAt) }}</dd>
                </div>
            </dl>
            <p class="rounded-md bg-muted/50 p-4 whitespace-pre-line">
                {{ message.message }}
            </p>
        </CardContent>
    </Card>

    <ConfirmDeleteDialog
        v-model:open="confirmingDelete"
        @confirm="confirmDelete"
    />
</template>
