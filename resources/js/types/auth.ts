export type UserRole = 'admin' | 'manager' | 'customer';

export type User = {
    id: string;
    name: string;
    email: string;
    role: UserRole;
    email_verified_at: string | null;
    avatar?: string;
};

export type Auth = {
    user: User | null;
};

export type Passkey = {
    id: number;
    name: string;
    authenticator: string | null;
    created_at_diff: string;
    last_used_at_diff: string | null;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
