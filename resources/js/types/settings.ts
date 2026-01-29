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
