export interface SiteCompany {
    name: string;
    about: string | null;
    phone: string | null;
    whatsapp: string | null;
    email: string | null;
    address: string | null;
    logoUrl: string;
}

export interface SiteSlider {
    id: string;
    title: string;
    subtitle: string | null;
    description: string | null;
    ctaLabel: string | null;
    ctaUrl: string | null;
    image: string | null;
}

export interface SiteProductCard {
    id: string;
    name: string;
    slug: string;
    reference: string | null;
    categoryName: string | null;
    image: string | null;
    widthCm: number | null;
    heightCm: number | null;
}

export interface SiteCategorySummary {
    id?: string;
    name: string;
    slug: string;
    productsCount?: number;
}

export interface SiteProductDetail {
    id: string;
    name: string;
    slug: string;
    url: string;
    reference: string | null;
    description: string | null;
    widthCm: number | null;
    heightCm: number | null;
    weightKg: string | null;
    category: { name: string; slug: string } | null;
    images: { id: string; url: string }[];
}

export interface SiteGalleryImage {
    id: string;
    url: string;
    productName: string | null;
    productSlug: string | null;
}
