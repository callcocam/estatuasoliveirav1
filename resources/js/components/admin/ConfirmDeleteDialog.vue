<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useT } from '@/composables/useT';

defineProps<{
    open: boolean;
    title?: string;
    description?: string;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    confirm: [];
}>();

const { t } = useT();
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{
                    title ?? t('app.admin.common.confirm_delete_title')
                }}</DialogTitle>
                <DialogDescription>
                    {{
                        description ??
                        t('app.admin.common.confirm_delete_description')
                    }}
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <DialogClose as-child>
                    <Button variant="outline" type="button">
                        {{ t('app.admin.common.cancel') }}
                    </Button>
                </DialogClose>
                <Button
                    variant="destructive"
                    type="button"
                    @click="emit('confirm')"
                >
                    {{ t('app.admin.common.delete') }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
