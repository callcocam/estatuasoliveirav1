<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    CircleHelp,
    Cog,
    ExternalLink,
    FolderTree,
    GalleryHorizontal,
    Inbox,
    LayoutGrid,
    Package,
    ReceiptText,
    Users,
} from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { useT } from '@/composables/useT';
import { home } from '@/routes';
import { dashboard, help } from '@/routes/admin';
import { index as categoriesIndex } from '@/routes/admin/categories';
import { index as messagesIndex } from '@/routes/admin/messages';
import { index as productsIndex } from '@/routes/admin/products';
import { index as quotesIndex } from '@/routes/admin/quotes';
import { edit as settingsEdit } from '@/routes/admin/settings';
import { index as slidersIndex } from '@/routes/admin/sliders';
import { index as usersIndex } from '@/routes/admin/users';
import type { NavItem } from '@/types';

const page = usePage();
const { t } = useT();
const { isCurrentUrl } = useCurrentUrl();

const isAdmin = computed(
    () => (page.props.auth.user?.role as string) === 'admin',
);

const groups = computed<{ label: string; items: NavItem[] }[]>(() => {
    const result = [
        {
            label: t('app.admin.nav.catalog'),
            items: [
                {
                    title: t('app.admin.nav.categories'),
                    href: categoriesIndex().url,
                    icon: FolderTree,
                },
                {
                    title: t('app.admin.nav.products'),
                    href: productsIndex().url,
                    icon: Package,
                },
                {
                    title: t('app.admin.nav.sliders'),
                    href: slidersIndex().url,
                    icon: GalleryHorizontal,
                },
            ],
        },
        {
            label: t('app.admin.nav.sales'),
            items: [
                {
                    title: t('app.admin.nav.quotes'),
                    href: quotesIndex().url,
                    icon: ReceiptText,
                },
                {
                    title: t('app.admin.nav.messages'),
                    href: messagesIndex().url,
                    icon: Inbox,
                },
            ],
        },
    ];

    if (isAdmin.value) {
        result.push({
            label: t('app.admin.nav.management'),
            items: [
                {
                    title: t('app.admin.nav.users'),
                    href: usersIndex().url,
                    icon: Users,
                },
                {
                    title: t('app.admin.nav.settings'),
                    href: settingsEdit().url,
                    icon: Cog,
                },
            ],
        });
    }

    return result;
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard().url">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <SidebarGroup class="px-2 py-0">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentUrl(dashboard().url)"
                            :tooltip="t('app.admin.nav.dashboard')"
                        >
                            <Link :href="dashboard().url">
                                <LayoutGrid />
                                <span>{{ t('app.admin.nav.dashboard') }}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>

            <SidebarGroup
                v-for="group in groups"
                :key="group.label"
                class="px-2 py-0"
            >
                <SidebarGroupLabel>{{ group.label }}</SidebarGroupLabel>
                <SidebarMenu>
                    <SidebarMenuItem
                        v-for="item in group.items"
                        :key="item.title"
                    >
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentUrl(item.href)"
                            :tooltip="item.title"
                        >
                            <Link :href="item.href">
                                <component :is="item.icon" />
                                <span>{{ item.title }}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton
                        as-child
                        :is-active="isCurrentUrl(help().url)"
                        :tooltip="t('app.admin.nav.help')"
                    >
                        <Link :href="help().url">
                            <CircleHelp />
                            <span>{{ t('app.admin.nav.help') }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
                <SidebarMenuItem>
                    <SidebarMenuButton
                        as-child
                        :tooltip="t('app.admin.nav.view_site')"
                    >
                        <Link :href="home().url">
                            <ExternalLink />
                            <span>{{ t('app.admin.nav.view_site') }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
