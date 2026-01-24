export interface Permission {
    id: number;
    name: string;
    description?: string;
}

export interface Role {
    id: number;
    name: string;
    permissions?: Permission[];
}

export interface User {
    id: number;
    name: string;
    email: string;
    roles?: Role[];
    permissions?: Permission[];
    email_verified_at?: string | null;
    created_at?: string;
    updated_at?: string;
    [key: string]: any;
}

export interface AuthState {
    user: User | null;
    isAuthenticated: boolean;
}

export interface LoginCredentials {
    email: string;
    password: string;
    remember?: boolean;
}

export interface AuthResponse {
    success: boolean;
    data?: any;
    message?: string;
    errors?: Record<string, string[]>;
    requiresTwoFactor?: boolean;
    requiresVerification?: boolean;
    rateLimited?: boolean;
    retryAfter?: number;
    userId?: number;
}
