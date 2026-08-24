<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { getInitials } from '@/composables/useInitials';
import { useT } from '@/composables/useT';
import { login, logout, register } from '@/routes';
import { dashboard as adminDashboard } from '@/routes/admin';
import { index as quotesIndex } from '@/routes/quotes';

withDefaults(defineProps<{ mobile?: boolean; topbar?: boolean }>(), {
    mobile: false,
    topbar: false,
});

const { t } = useT();
const page = usePage();

const user = computed(() => page.props.auth.user);
const isStaff = computed(
    () => user.value?.role === 'admin' || user.value?.role === 'manager',
);

const handleLogout = () => {
    router.flushAll();
};
</script>

<template>
    <!-- Versão mobile: links achatados dentro do menu hambúrguer -->
    <div v-if="mobile">
        <template v-if="!user">
            <Link
                :href="login()"
                class="block rounded-site px-3 py-3 text-base text-site-on-surface-variant hover:bg-site-surface-container"
            >
                {{ t('app.site.auth.login') }}
            </Link>
            <Link
                :href="register()"
                class="block rounded-site px-3 py-3 text-base text-site-on-surface-variant hover:bg-site-surface-container"
            >
                {{ t('app.site.auth.register') }}
            </Link>
        </template>
        <template v-else>
            <p
                class="px-3 pt-3 pb-1 text-xs font-semibold tracking-widest text-site-on-surface-variant uppercase"
            >
                {{ user.name }}
            </p>
            <Link
                v-if="!isStaff"
                :href="quotesIndex()"
                class="block rounded-site px-3 py-3 text-base text-site-on-surface-variant hover:bg-site-surface-container"
            >
                {{ t('app.site.auth.my_quotes') }}
            </Link>
            <Link
                v-if="isStaff"
                :href="adminDashboard()"
                class="block rounded-site px-3 py-3 text-base text-site-on-surface-variant hover:bg-site-surface-container"
            >
                {{ t('app.site.auth.admin_panel') }}
            </Link>
            <Link
                :href="logout()"
                as="button"
                class="block w-full rounded-site px-3 py-3 text-left text-base text-site-on-surface-variant hover:bg-site-surface-container"
                @click="handleLogout"
            >
                {{ t('app.site.auth.logout') }}
            </Link>
        </template>
    </div>

    <!-- Versão desktop (padrão) ou compacta para a barra superior -->
    <div v-else :class="['flex items-center', topbar ? 'gap-2' : 'gap-1']">
        <template v-if="!user">
            <Link
                :href="login()"
                :class="
                    topbar
                        ? 'rounded-full px-2.5 py-1 text-xs font-medium opacity-90 transition-opacity hover:opacity-100'
                        : 'rounded-site px-3 py-2 text-sm font-medium text-site-on-surface-variant transition-colors hover:bg-site-surface-container hover:text-site-on-surface'
                "
            >
                {{ t('app.site.auth.login') }}
            </Link>
            <Link
                :href="register()"
                :class="
                    topbar
                        ? 'rounded-full border border-current/40 px-3 py-1 text-xs font-medium transition-opacity hover:opacity-80'
                        : 'rounded-site border border-site-outline-variant px-4 py-2 text-sm font-medium text-site-primary transition-colors hover:bg-site-surface-container'
                "
            >
                {{ t('app.site.auth.register') }}
            </Link>
        </template>
        <DropdownMenu v-else>
            <DropdownMenuTrigger
                :class="[
                    'flex items-center justify-center rounded-full bg-site-secondary-container font-semibold text-site-on-secondary-container transition-shadow hover:ring-2 hover:ring-site-primary/40',
                    topbar ? 'h-7 w-7 text-xs' : 'h-9 w-9 text-sm',
                ]"
                :aria-label="t('app.site.auth.user_menu')"
            >
                {{ getInitials(user.name) }}
            </DropdownMenuTrigger>
            <DropdownMenuContent
                align="end"
                class="w-56 rounded-site border-site-outline-variant bg-site-surface text-site-on-surface"
            >
                <DropdownMenuLabel class="font-normal">
                    <p
                        class="truncate text-sm font-medium text-site-on-surface"
                    >
                        {{ user.name }}
                    </p>
                    <p class="truncate text-xs text-site-on-surface-variant">
                        {{ user.email }}
                    </p>
                </DropdownMenuLabel>
                <DropdownMenuSeparator class="bg-site-outline-variant" />
                <DropdownMenuItem v-if="!isStaff" :as-child="true">
                    <Link
                        :href="quotesIndex()"
                        class="block w-full cursor-pointer focus:bg-site-surface-container focus:text-site-on-surface"
                    >
                        {{ t('app.site.auth.my_quotes') }}
                    </Link>
                </DropdownMenuItem>
                <DropdownMenuItem v-if="isStaff" :as-child="true">
                    <Link
                        :href="adminDashboard()"
                        class="block w-full cursor-pointer focus:bg-site-surface-container focus:text-site-on-surface"
                    >
                        {{ t('app.site.auth.admin_panel') }}
                    </Link>
                </DropdownMenuItem>
                <DropdownMenuSeparator class="bg-site-outline-variant" />
                <DropdownMenuItem :as-child="true">
                    <Link
                        :href="logout()"
                        as="button"
                        class="block w-full cursor-pointer text-left focus:bg-site-surface-container focus:text-site-on-surface"
                        @click="handleLogout"
                    >
                        {{ t('app.site.auth.logout') }}
                    </Link>
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    </div>
</template>
