<script setup lang="ts">
import { Info } from '@lucide/vue';
import { computed } from 'vue';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { useT } from '@/composables/useT';
import type { TeamInvitationContext } from '@/types';

type Props = {
    invitation: TeamInvitationContext;
    action: 'Log in' | 'Register';
};

const props = defineProps<Props>();

const { t } = useT();

const message = computed(() =>
    t('app.teams.invitation_alert.message', {
        action: t(
            props.action === 'Log in'
                ? 'app.teams.invitation_alert.actions.log_in'
                : 'app.teams.invitation_alert.actions.register',
        ),
        team: props.invitation.teamName,
    }),
);
</script>

<template>
    <div data-test="team-invitation-alert">
        <Alert
            class="border-blue-200 bg-blue-50 text-blue-900 dark:border-blue-900/50 dark:bg-blue-950/50 dark:text-blue-100 [&>svg]:text-blue-600 dark:[&>svg]:text-blue-400"
        >
            <Info class="size-4" />
            <AlertDescription class="text-blue-900 dark:text-blue-100">
                {{ message }}
            </AlertDescription>
        </Alert>
    </div>
</template>
