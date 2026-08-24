<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
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
import { destroy as destroyInvitation } from '@/routes/teams/invitations';
import type { Team, TeamInvitation } from '@/types';

type Props = {
    team: Team;
    invitation: TeamInvitation | null;
    open: boolean;
};

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const { t } = useT();

const processing = ref(false);

const cancelInvitation = () => {
    if (!props.invitation) {
        return;
    }

    router.visit(destroyInvitation([props.team.slug, props.invitation.code]), {
        onStart: () => (processing.value = true),
        onFinish: () => (processing.value = false),
        onSuccess: () => emit('update:open', false),
    });
};
</script>

<template>
    <Dialog :open="props.open" @update:open="emit('update:open', $event)">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{
                    t('app.teams.modals.cancel_invitation.title')
                }}</DialogTitle>
                <DialogDescription>
                    {{
                        t(
                            'app.teams.modals.cancel_invitation.description_prefix',
                        )
                    }}
                    <strong>{{ props.invitation?.email }}</strong
                    >{{
                        t(
                            'app.teams.modals.cancel_invitation.description_suffix',
                        )
                    }}
                </DialogDescription>
            </DialogHeader>

            <DialogFooter class="gap-2">
                <DialogClose as-child>
                    <Button variant="secondary">
                        {{ t('app.teams.modals.cancel_invitation.keep') }}
                    </Button>
                </DialogClose>

                <Button
                    data-test="cancel-invitation-confirm"
                    variant="destructive"
                    :disabled="processing"
                    @click="cancelInvitation"
                >
                    {{ t('app.teams.modals.cancel_invitation.submit') }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
