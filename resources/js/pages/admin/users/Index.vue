<script setup lang="ts">
import { Form, Head, router, usePage } from '@inertiajs/vue3';
import { KeyRound, Pencil, Plus, RotateCcw, Trash2 } from '@lucide/vue';
import { computed, ref } from 'vue';
import AdminPagination from '@/components/admin/AdminPagination.vue';
import ConfirmDeleteDialog from '@/components/admin/ConfirmDeleteDialog.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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
    destroy as userDestroy,
    index as usersIndex,
    resetLink as userResetLink,
    restore as userRestore,
    store as userStore,
    update as userUpdate,
} from '@/routes/admin/users';
import type { Paginated } from '@/types/admin';

defineOptions({ layout: AdminLayout });

type UserRow = {
    id: string;
    name: string;
    email: string;
    phone: string | null;
    role: string;
    roleLabel: string;
    createdAt: string | null;
    deleted: boolean;
};

const props = defineProps<{
    users: Paginated<UserRow>;
    filters: { search: string | null; filter: string | null };
}>();

const page = usePage();
const { t } = useT();

const currentUserId = computed(() => String(page.props.auth.user?.id ?? ''));

const search = ref(props.filters.search ?? '');
const filter = ref(props.filters.filter ?? 'all');
const formOpen = ref(false);
const editing = ref<UserRow | null>(null);
const roleValue = ref('customer');
const deleting = ref<UserRow | null>(null);

function applyFilters() {
    router.get(
        usersIndex().url,
        {
            search: search.value || undefined,
            filter: filter.value !== 'all' ? filter.value : undefined,
        },
        { preserveState: true, preserveScroll: true },
    );
}

function openCreate() {
    editing.value = null;
    roleValue.value = 'customer';
    formOpen.value = true;
}

function openEdit(user: UserRow) {
    editing.value = user;
    roleValue.value = user.role;
    formOpen.value = true;
}

function confirmDelete() {
    if (!deleting.value) {
        return;
    }

    router.delete(userDestroy(deleting.value.id).url, {
        preserveScroll: true,
        onFinish: () => (deleting.value = null),
    });
}
</script>

<template>
    <Head :title="t('app.admin.users.title')" />

    <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold">{{ t('app.admin.users.title') }}</h1>
        <Button type="button" @click="openCreate">
            <Plus />
            {{ t('app.admin.users.new') }}
        </Button>
    </div>

    <div class="flex items-center gap-3">
        <Input
            v-model="search"
            type="search"
            :placeholder="t('app.admin.common.search_placeholder')"
            class="max-w-xs"
            @keydown.enter="applyFilters"
        />
        <Select v-model="filter" @update:model-value="applyFilters">
            <SelectTrigger class="w-44">
                <SelectValue />
            </SelectTrigger>
            <SelectContent>
                <SelectItem value="all">
                    {{ t('app.admin.common.filter_all') }}
                </SelectItem>
                <SelectItem value="trashed">
                    {{ t('app.admin.common.filter_trashed') }}
                </SelectItem>
            </SelectContent>
        </Select>
    </div>

    <div class="overflow-x-auto rounded-lg border">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b bg-muted/50 text-left">
                    <th class="px-4 py-3 font-medium">
                        {{ t('app.admin.users.fields.name') }}
                    </th>
                    <th class="px-4 py-3 font-medium">
                        {{ t('app.admin.users.fields.role') }}
                    </th>
                    <th class="px-4 py-3 text-right font-medium">
                        {{ t('app.admin.common.actions') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="users.data.length === 0">
                    <td
                        colspan="3"
                        class="px-4 py-8 text-center text-muted-foreground"
                    >
                        {{ t('app.admin.common.empty') }}
                    </td>
                </tr>
                <tr
                    v-for="user in users.data"
                    :key="user.id"
                    class="border-b last:border-b-0"
                >
                    <td class="px-4 py-3">
                        <p class="font-medium">{{ user.name }}</p>
                        <p class="text-xs text-muted-foreground">
                            {{ user.email }}
                        </p>
                    </td>
                    <td class="px-4 py-3">
                        <Badge
                            :variant="
                                user.role === 'admin' ? 'default' : 'secondary'
                            "
                        >
                            {{ user.roleLabel }}
                        </Badge>
                        <Badge
                            v-if="user.deleted"
                            variant="destructive"
                            class="ml-1"
                        >
                            {{ t('app.admin.common.deleted_badge') }}
                        </Badge>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-1">
                            <template v-if="user.deleted">
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="icon"
                                    :aria-label="t('app.admin.common.restore')"
                                    @click="
                                        router.post(
                                            userRestore(user.id).url,
                                            {},
                                            { preserveScroll: true },
                                        )
                                    "
                                >
                                    <RotateCcw />
                                </Button>
                            </template>
                            <template v-else>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="icon"
                                    :aria-label="
                                        t('app.admin.users.send_reset_link')
                                    "
                                    @click="
                                        router.post(
                                            userResetLink(user.id).url,
                                            {},
                                            { preserveScroll: true },
                                        )
                                    "
                                >
                                    <KeyRound />
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="icon"
                                    :aria-label="t('app.admin.common.edit')"
                                    @click="openEdit(user)"
                                >
                                    <Pencil />
                                </Button>
                                <Button
                                    type="button"
                                    variant="destructive"
                                    size="icon"
                                    :disabled="user.id === currentUserId"
                                    :aria-label="t('app.admin.common.delete')"
                                    @click="deleting = user"
                                >
                                    <Trash2 />
                                </Button>
                            </template>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <AdminPagination :links="users.links" />

    <Dialog v-model:open="formOpen">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>
                    {{
                        editing
                            ? t('app.admin.users.edit')
                            : t('app.admin.users.new')
                    }}
                </DialogTitle>
            </DialogHeader>
            <Form
                :key="editing?.id ?? 'create'"
                v-bind="
                    editing ? userUpdate.form(editing.id) : userStore.form()
                "
                class="space-y-4"
                :options="{ preserveScroll: true }"
                @success="formOpen = false"
                #default="{ errors, processing }"
            >
                <div class="grid gap-2">
                    <Label for="user-name">{{
                        t('app.admin.users.fields.name')
                    }}</Label>
                    <Input
                        id="user-name"
                        name="name"
                        :default-value="editing?.name ?? ''"
                        required
                    />
                    <InputError :message="errors.name" />
                </div>
                <div class="grid gap-2">
                    <Label for="user-email">{{
                        t('app.admin.users.fields.email')
                    }}</Label>
                    <Input
                        id="user-email"
                        name="email"
                        type="email"
                        :default-value="editing?.email ?? ''"
                        required
                    />
                    <InputError :message="errors.email" />
                </div>
                <div class="grid gap-2">
                    <Label for="user-phone">{{
                        t('app.admin.users.fields.phone')
                    }}</Label>
                    <Input
                        id="user-phone"
                        name="phone"
                        :default-value="editing?.phone ?? ''"
                    />
                    <InputError :message="errors.phone" />
                </div>
                <div class="grid gap-2">
                    <Label>{{ t('app.admin.users.fields.role') }}</Label>
                    <Select v-model="roleValue" name="role">
                        <SelectTrigger>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="admin">Administrador</SelectItem>
                            <SelectItem value="manager">Gerente</SelectItem>
                            <SelectItem value="customer">Cliente</SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="errors.role" />
                </div>
                <div class="grid gap-2">
                    <Label for="user-password">{{
                        t('app.admin.users.fields.password')
                    }}</Label>
                    <Input
                        id="user-password"
                        name="password"
                        type="password"
                        autocomplete="new-password"
                    />
                    <p v-if="editing" class="text-xs text-muted-foreground">
                        {{ t('app.admin.users.fields.password_hint') }}
                    </p>
                    <InputError :message="errors.password" />
                </div>
                <div class="flex justify-end gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        @click="formOpen = false"
                    >
                        {{ t('app.admin.common.cancel') }}
                    </Button>
                    <Button type="submit" :disabled="processing">
                        {{
                            processing
                                ? t('app.admin.common.saving')
                                : t('app.admin.common.save')
                        }}
                    </Button>
                </div>
            </Form>
        </DialogContent>
    </Dialog>

    <ConfirmDeleteDialog
        :open="deleting !== null"
        @update:open="deleting = $event ? deleting : null"
        @confirm="confirmDelete"
    />
</template>
