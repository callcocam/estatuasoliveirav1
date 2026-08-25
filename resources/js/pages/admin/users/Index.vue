<script setup lang="ts">
import { Form, Head, router, usePage } from '@inertiajs/vue3';
import { KeyRound, Pencil, Plus } from '@lucide/vue';
import { computed, ref } from 'vue';
import DeleteButton from '@/components/admin/DeleteButton.vue';
import ListFiltersBar from '@/components/admin/ListFiltersBar.vue';
import ListPage from '@/components/admin/ListPage.vue';
import RestoreButton from '@/components/admin/RestoreButton.vue';
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
import { useDeferredPaginator } from '@/composables/useDeferredPaginator';
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
import type { Paginated, ResourceAbilities, UserRow } from '@/types/admin';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    users?: Paginated<UserRow>;
    roles: { value: string; label: string }[];
    filters: {
        search: string;
        role: string;
        trashed: string;
        per_page: string;
    };
    can: ResourceAbilities;
}>();

const page = usePage();
const { t } = useT();

const currentUserId = computed(() => String(page.props.auth.user?.id ?? ''));

const { isLoading, isEmpty, rows, links } = useDeferredPaginator<UserRow>(
    () => props.users,
);

const formOpen = ref(false);
const editing = ref<UserRow | null>(null);
const roleValue = ref('customer');

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

function sendResetLink(user: UserRow) {
    router.post(userResetLink(user.id).url, {}, { preserveScroll: true });
}
</script>

<template>
    <Head :title="t('app.admin.users.title')" />

    <ListPage
        :title="t('app.admin.users.title')"
        :loading="isLoading"
        :empty="isEmpty"
        :columns="4"
        :links="links"
    >
        <template #actions>
            <Button v-if="can.create" type="button" @click="openCreate">
                <Plus class="size-4" />
                {{ t('app.admin.users.new') }}
            </Button>
        </template>

        <template #filters>
            <ListFiltersBar
                :index-url="usersIndex().url"
                :filters="{
                    search: filters.search,
                    role: filters.role || 'all',
                    trashed: filters.trashed,
                    per_page: filters.per_page,
                }"
                :search-placeholder="t('app.admin.common.search_placeholder')"
            >
                <template #default="{ values, set }">
                    <Select
                        :model-value="values.role"
                        @update:model-value="set('role', String($event ?? 'all'))"
                    >
                        <SelectTrigger
                            class="w-44"
                            :aria-label="t('app.admin.users.fields.role')"
                        >
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">
                                {{ t('app.admin.users.filter_all_roles') }}
                            </SelectItem>
                            <SelectItem
                                v-for="option in roles"
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
            <th class="px-4 py-3 font-medium">{{ t('app.admin.common.name') }}</th>
            <th class="hidden px-4 py-3 font-medium md:table-cell">
                {{ t('app.admin.users.fields.phone') }}
            </th>
            <th class="px-4 py-3 font-medium">
                {{ t('app.admin.users.fields.role') }}
            </th>
            <th class="px-4 py-3 text-right font-medium">
                {{ t('app.admin.common.actions') }}
            </th>
        </template>

        <template #body>
            <tr
                v-for="user in rows"
                :key="user.id"
                class="border-b border-border last:border-b-0"
                :class="{ 'opacity-60': user.deleted }"
            >
                <td class="px-4 py-3">
                    <p class="font-medium text-foreground">{{ user.name }}</p>
                    <p class="text-xs text-muted-foreground">{{ user.email }}</p>
                </td>
                <td class="hidden px-4 py-3 md:table-cell">
                    {{ user.phone ?? t('app.admin.common.none') }}
                </td>
                <td class="px-4 py-3">
                    <div class="flex flex-wrap items-center gap-1">
                        <Badge
                            :variant="
                                user.role === 'admin' ? 'default' : 'secondary'
                            "
                        >
                            {{ user.roleLabel }}
                        </Badge>
                        <Badge v-if="user.deleted" variant="destructive">
                            {{ t('app.admin.common.deleted_badge') }}
                        </Badge>
                    </div>
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center justify-end gap-2">
                        <template v-if="!user.deleted">
                            <Button
                                v-if="can.update"
                                size="icon"
                                variant="outline"
                                type="button"
                                :aria-label="t('app.admin.users.send_reset_link')"
                                @click="sendResetLink(user)"
                            >
                                <KeyRound class="size-4" />
                            </Button>
                            <Button
                                v-if="can.update"
                                size="icon"
                                variant="outline"
                                type="button"
                                :aria-label="t('app.admin.common.edit')"
                                @click="openEdit(user)"
                            >
                                <Pencil class="size-4" />
                            </Button>
                            <DeleteButton
                                v-if="can.delete && user.id !== currentUserId"
                                :href="userDestroy(user.id).url"
                            />
                        </template>
                        <template v-else>
                            <RestoreButton
                                v-if="can.delete"
                                :href="userRestore(user.id).url"
                            />
                            <DeleteButton
                                v-if="can.delete"
                                :href="userDestroy(user.id).url"
                                permanent
                            />
                        </template>
                    </div>
                </td>
            </tr>
        </template>
    </ListPage>

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
                        <SelectTrigger class="w-full">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="option in roles"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
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
</template>
