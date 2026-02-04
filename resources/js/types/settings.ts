export interface SiteSettings {
    site_name: string;
    site_description: string;
    site_url: string;
    admin_email: string;
    site_version: string;
    site_logo: string;
    site_favicon: string;
    site_footer?: string;
    site_analytics_id?: string;
    maintenance_mode?: boolean;
    contact_email?: string;
    contact_phone?: string;
    contact_address?: string;
}

export interface CacheStatus {
    driver: string;
    enabled: boolean;
    keys: number | string;
    size: string;
}

export interface QueueStatus {
    driver: string;
    connection: string;
    pending_jobs: number | string;
    failed_jobs: number | string;
}

export interface EmailLog {
    to: string;
    subject: string;
    sent_at: string;
}

export type SettingValue = string | number | boolean | null;
