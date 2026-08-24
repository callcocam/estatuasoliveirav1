import type { SiteCompany } from '@/types/site';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

/**
 * Company profile shared by HandleInertiaRequests as the `site` prop
 * (data comes from the settings table via App\Support\CompanyProfile).
 */
export function useCompany() {
    const page = usePage();

    const company = computed<SiteCompany>(
        () =>
            (page.props.site as SiteCompany | undefined) ?? {
                name: 'Estátuas Oliveira',
                about: null,
                phone: null,
                whatsapp: null,
                email: null,
                address: null,
            },
    );

    const whatsappUrl = computed<string | null>(() => {
        const digits = company.value.whatsapp?.replace(/\D/g, '') ?? '';

        if (digits === '') {
            return null;
        }

        return `https://wa.me/${digits.startsWith('55') ? digits : `55${digits}`}`;
    });

    return { company, whatsappUrl };
}
