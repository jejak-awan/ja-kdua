import type { User } from './auth';
import type { BlockInstance } from './builder';

/**
 * @deprecated Legacy form field interface. Use visual builder blocks instead.
 * Kept for backward compatibility with existing data and migrations.
 */
export interface FormField {
    id?: string | number;
    name: string;
    type: string;
    label: string;
    description?: string;
    placeholder?: string;
    default_value?: any;
    options?: any[];
    validation_rules?: string | Record<string, any>;
    is_required?: boolean;
    help_text?: string;
    order?: number;
    settings?: Record<string, any>;
}

export interface Form {
    id: number;
    name: string;
    slug: string;
    description?: string | null;
    success_message?: string | null;
    redirect_url?: string | null;
    is_active: boolean;
    /** @deprecated Use blocks instead */
    fields?: FormField[];
    blocks?: BlockInstance[];
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
