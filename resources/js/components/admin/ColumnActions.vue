<script setup lang="ts">
import DeleteButton from '@/components/admin/DeleteButton.vue';
import EditButton from '@/components/admin/EditButton.vue';
import RestoreButton from '@/components/admin/RestoreButton.vue';

withDefaults(
    defineProps<{
        trashed: boolean;
        editHref?: string;
        deleteHref?: string;
        restoreHref?: string;
        canUpdate?: boolean;
        canDelete?: boolean;
    }>(),
    {
        canUpdate: true,
        canDelete: true,
    },
);
</script>

<template>
    <div class="flex items-center justify-end gap-2">
        <template v-if="!trashed">
            <EditButton v-if="canUpdate && editHref" :href="editHref" />
            <slot />
            <DeleteButton v-if="canDelete && deleteHref" :href="deleteHref" />
        </template>
        <template v-else>
            <RestoreButton
                v-if="canDelete && restoreHref"
                :href="restoreHref"
            />
            <DeleteButton
                v-if="canDelete && deleteHref"
                :href="deleteHref"
                permanent
            />
        </template>
    </div>
</template>
