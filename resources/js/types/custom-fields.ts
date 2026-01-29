export interface FieldGroup {
    id: number;
    name: string;
    description?: string | null;
    fields_count?: number;
    attachable_type?: string | null;
    created_at?: string;
    updated_at?: string;
}

export interface CustomField {
    id: number;
    field_group_id: number;
    label: string;
    name: string;
    type: string;
    description?: string | null;
    placeholder?: string | null;
    default_value?: any;
    options?: any[];
    validation_rules?: string | Record<string, any>;
    is_required?: boolean;
    order?: number;
    settings?: Record<string, any>;
    created_at?: string;
    updated_at?: string;

    // Relations
    field_group?: FieldGroup;
}
