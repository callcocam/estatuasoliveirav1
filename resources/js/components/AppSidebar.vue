<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    ExternalLink,
    FileText,
    LayoutGrid,
    Palette,
    ShieldCheck,
    UserRound,
} from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useT } from '@/composables/useT';
import { home } from '@/routes';
import { dashboard as adminDashboard } from '@/routes/admin';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { index as quotesIndex } from '@/routes/quotes';
import { edit as editSecurity } from '@/routes/security';
import type { NavItem } from '@/types';

const page = usePage();
const { t } = useT();

const isCustomer = computed(() => page.props.auth.user?.role === 'customer');

const homeHref = computed(() =>
    isCustomer.value ? quotesIndex() : adminDashboard(),
);

const mainNavItems = computed<NavItem[]>(() => [
    isCustomer.value
        ? {
              title: t('app.nav.items.quotes'),
              href: quotesIndex(),
              icon: FileText,
          }
        : {
              title: t('app.nav.items.admin_panel'),
              href: adminDashboard(),
              icon: LayoutGrid,
          },
    {
        title: t('app.settings.layout.nav.profile'),
        href: editProfile(),
        icon: UserRound,
    },
    {
        title: t('app.settings.layout.nav.security'),
        href: editSecurity(),
        icon: ShieldCheck,
    },
    {
        title: t('app.settings.layout.nav.appearance'),
        href: editAppearance(),
        icon: Palette,
    },
]);
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="homeHref">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton
                        as-child
                        :tooltip="t('app.nav.items.back_to_site')"
                    >
                        <Link :href="home()">
                            <ExternalLink />
                            <span>{{ t('app.nav.items.back_to_site') }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
