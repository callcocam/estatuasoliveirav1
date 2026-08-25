<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Trash2 } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { useT } from '@/composables/useT';
import { randomConfirmWord } from '@/support/confirmWords';

const props = withDefaults(
    defineProps<{
        href: string;
        permanent?: boolean;
    }>(),
    { permanent: false },
);

const { t } = useT();

const open = ref(false);
const word = ref(randomConfirmWord());
const typed = ref('');
const deleting = ref(false);

watch(open, (value) => {
    if (value) {
        word.value = randomConfirmWord();
        typed.value = '';
    }
});

const canConfirm = computed(
    () => typed.value.trim().toLowerCase() === word.value,
);

function confirmDelete(): void {
    if (!canConfirm.value) {
        return;
    }

    router.delete(props.href, {
        preserveScroll: true,
        onStart: () => (deleting.value = true),
        onFinish: () => {
            deleting.value = false;
            open.value = false;
        },
    });
}
</script>

<template>
    <Dialog v-model:open="open">
        <DialogTrigger as-child>
            <Button
                size="icon"
                variant="destructive"
                type="button"
                :aria-label="
                    permanent
                        ? t('app.admin.common.delete_permanently')
                        : t('app.admin.common.delete')
                "
            >
                <Trash2 class="size-4" />
            </Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>
                    {{
                        permanent
                            ? t(
                                  'app.admin.common.delete_dialog.title_permanent',
                              )
                            : t('app.admin.common.delete_dialog.title')
                    }}
                </DialogTitle>
                <DialogDescription>
                    {{
                        permanent
                            ? t(
                                  'app.admin.common.delete_dialog.description_permanent',
                              )
                            : t('app.admin.common.delete_dialog.description', {
                                  filter: t(
                                      'app.admin.common.filter_trashed_only',
                                  ),
                              })
                    }}
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-2">
                <p class="text-sm text-foreground">
                    {{ t('app.admin.common.delete_dialog.confirm_prompt') }}
                    <span
                        class="rounded bg-muted px-1.5 py-0.5 font-mono font-semibold"
                        >{{ word }}</span
                    >
                </p>
                <Input
                    v-model="typed"
                    type="text"
                    autocomplete="off"
                    :placeholder="word"
                    @keyup.enter="confirmDelete"
                />
            </div>

            <DialogFooter>
                <DialogClose as-child>
                    <Button variant="outline" type="button">
                        {{ t('app.admin.common.cancel') }}
                    </Button>
                </DialogClose>
                <Button
                    variant="destructive"
                    type="button"
                    :disabled="!canConfirm || deleting"
                    @click="confirmDelete"
                >
                    {{
                        permanent
                            ? t('app.admin.common.delete_permanently')
                            : t('app.admin.common.delete')
                    }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
