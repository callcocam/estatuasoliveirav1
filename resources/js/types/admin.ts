export type Paginated<T> = {
    data: T[];
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
    total: number;
};

export type ResourceAbilities = {
    create: boolean;
    update: boolean;
    delete: boolean;
};

export type TrashedFilter = 'without' | 'only' | 'with';

export type ContactMessageRow = {
    id: string;
    name: string;
    email: string;
    subject: string | null;
    read: boolean;
    createdAt: string | null;
    deleted: boolean;
};

export type QuoteRow = {
    id: string;
    userName: string | null;
    status: string;
    statusLabel: string;
    total: string;
    itemsCount: number;
    createdAt: string | null;
    deleted: boolean;
};

export type UserRow = {
    id: string;
    name: string;
    email: string;
    phone: string | null;
    role: string;
    roleLabel: string;
    createdAt: string | null;
    deleted: boolean;
};

export type ProductRow = {
    id: string;
    name: string;
    slug: string;
    reference: string | null;
    categoryName: string | null;
    status: string;
    statusLabel: string;
    featured: boolean;
    stock: number;
    image: string | null;
    deleted: boolean;
};
