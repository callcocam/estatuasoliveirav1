import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useT } from '@/composables/useT';
import type { SiteCompany } from '@/types/site';

/**
 * Company profile shared by HandleInertiaRequests as the `site` prop
 * (data comes from the settings table via App\Support\CompanyProfile).
 */
export function useCompany() {
    const page = usePage();
    const { t } = useT();

    const company = computed<SiteCompany>(
        () =>
            (page.props.site as SiteCompany | undefined) ?? {
                name: 'Estátuas Oliveira',
                about: null,
                phone: null,
                whatsapp: null,
                email: null,
                address: null,
                logoUrl: '/images/logo.png',
                url: '/',
            },
    );

    const whatsappUrl = computed<string | null>(() => {
        const digits = company.value.whatsapp?.replace(/\D/g, '') ?? '';

        if (digits === '') {
            return null;
        }

        return `https://wa.me/${digits.startsWith('55') ? digits : `55${digits}`}`;
    });

    /**
     * WhatsApp link with a prefilled greeting identifying the site
     * (company name + URL), for header/footer/direct contact links.
     */
    const whatsappUrlWithMessage = computed<string | null>(() => {
        if (!whatsappUrl.value) {
            return null;
        }

        const message = t('app.site.whatsapp.default_message', {
            company: company.value.name,
            url: company.value.url,
        });

        return `${whatsappUrl.value}?text=${encodeURIComponent(message)}`;
    });

    return { company, whatsappUrl, whatsappUrlWithMessage };
}
