import type { User } from './auth';

export interface FormField {
    id: string | number;
    name: string;
    type: string;
    label: string;
    description?: string;
    placeholder?: string;
    default_value?: any;
    options?: any[];
    validation_rules?: string | Record<string, any>;
    is_required?: boolean;
    order?: number;
    settings?: Record<string, any>;
}

export interface Form {
    id: number;
    name: string;
    slug: string;
    description?: string | null;
    is_active: boolean;
    fields: FormField[];
    submission_count?: number;
    settings?: Record<string, any>;
    deleted_at?: string | null;
    created_at?: string;
    updated_at?: string;
}

export interface FormSubmission {
    id: number;
    form_id: number;
    data: Record<string, any>;
    ip_address?: string;
    user_agent?: string;
    status: 'new' | 'read' | 'archived';
    user_id?: number | null;
    user?: User | null;
    created_at: string;
    updated_at: string;
}

export interface FormStatistics {
    total: number;
    new: number;
    read: number;
    archived: number;
}
