import type { User } from './auth';

export interface Customer {
    id: number;
    user_id: number;
    identity_number?: string;
    identity_type?: string;
    address_street?: string;
    address_village?: string;
    address_district?: string;
    address_city?: string;
    address_province?: string;
    address_postal_code?: string;
    coordinates?: string;
    billing_plan_id?: number;
    billing_cycle_start?: number;
    installation_date?: string;
    status: 'active' | 'isolated' | 'inactive' | 'suspended';
    mikrotik_login?: string;
    mikrotik_password?: string;
    reseller_id?: number;
    router_id?: number;
    server_id?: number;
    service_category?: string;
    billing_due_date?: string;
    billing_notes?: string;
    is_taxed?: boolean;
    unique_code?: number;
    address_list?: string;
    plan?: BillingPlan;
    current_usage_bytes?: number;
    last_usage_reset_at?: string;
    is_fup_active?: boolean;
    // Common appends/display fields
    name?: string;
    balance?: number;
    router_name?: string;
    ip_address?: string;
}

export interface IspUser extends User {
    customer?: Customer;
    isp_customer?: Customer; // Legacy support during migration
}

export interface IspDevice {
    id: number;
    type: string;
    mac_address: string;
    metadata?: {
        plan_name?: string;
        speed?: string;
        ip_address?: string;
    };
}

export interface IspConnection {
    status: 'online' | 'offline';
    signal_strength?: string;
    last_latency?: string;
    uptime?: string;
}

export interface IspOutage {
    id: number;
    title: string;
    status: string;
}

export type IspStatus = 'active' | 'isolated' | 'inactive' | 'suspended' | 'pending' | 'cancelled' | 'paid' | 'unpaid';

export interface IspInvoice {
    id: number;
    billing_period: string;
    amount: number;
    due_date: string;
    status: 'paid' | 'unpaid' | 'cancelled';
    user?: {
        id: number;
        name: string;
        email: string;
        phone?: string;
    };
}

export interface IspTrafficData {
    time?: string;
    date?: string;
    in: number;
    out: number;
    down: number;
    up: number;
}

export interface IspMemberDashboard {
    user?: {
        name: string;
        email: string;
    };
    customer?: Customer;
    connection?: IspConnection;
    device?: IspDevice;
    active_outages?: IspOutage[];
    unpaid_balance: number;
    invoices?: IspInvoice[];
    traffic_history?: IspTrafficData[];
    fup?: {
        enabled: boolean;
        limit_gb: number;
        usage_gb: number;
        is_throttled: boolean;
        throttled_speed?: string;
    };
}

export interface IspSupportTicket {
    id: number;
    subject: string;
    category: string;
    priority: string;
    status: string;
    message: string;
    created_at: string;
    admin_response?: string;
    user_id?: number;
    user?: {
        id: number;
        name: string;
        email: string;
    };
}

export interface IspUsageHistory {
    daily: IspTrafficData[];
    monthly: IspTrafficData[];
}

export interface IspUsageData {
    usage: IspUsageHistory;
    connection?: {
        status: 'connected' | 'disconnected';
        latency?: string;
    };
}

export interface NetworkNode {
    id: number;
    name: string;
    type: string;
    ip_address: string;
    secret?: string;
    connection_type?: 'IP PUBLIC' | 'VPN RADIUS';
    management_port?: number;
    connection_method?: 'none' | 'snmp' | 'api';
    api_username?: string;
    api_password?: string;
    api_port?: number;
    snmp_community?: string;
    snmp_port?: number;
    last_active_count?: number;
    is_connected?: boolean;
    status: string;
    location_lat: number;
    location_lng: number;
    metadata?: Record<string, unknown>;
}

export interface DataServer {
    id: number;
    name: string;
    status: string;
}

export interface MonitorNode {
    node_id: number;
    name: string;
    status: string;
    latency: number;
    cpu_load: number;
    traffic_in: number;
    traffic_out: number;
}

export interface IspMonitorHistoryPoint {
    time: string;
    in: number;
    out: number;
}

export interface IspMonitorStats {
    total_traffic_in: number;
    total_traffic_out: number;
    active_customers: number;
    network_health: string;
}

export interface ServiceRequest {
    id: number;
    customer_id: number;
    type: string;
    status: string;
    description?: string;
    created_at: string;
    customer?: {
        name: string;
        email: string;
    };
}

export interface BillingPlan {
    id: number;
    name: string;
    description?: string;
    mikrotik_group?: string;
    speed_limit?: string; // Legacy
    mikrotik_rate_limit?: string;
    shared_users?: number;
    active_period?: number;
    type: string;
    price: number;
    cost_price: number;
    commission: number;
    fup_limit_gb?: number;
    fup_speed?: string;
    fup_enabled?: boolean;
    is_active: boolean;
    features: string[];
}
export interface NetworkSubnet {
    id: number;
    node_id: number;
    name: string;
    prefix: string;
    gateway: string;
    vlan_id: number;
    type: string;
    node?: { name: string };
}

export interface IpAddress {
    id: number;
    address: string;
    status: string;
    notes: string | null;
    device?: {
        serial_number: string;
        customer?: { name: string };
    };
}

export interface Outage {
    id: number;
    node_id: number | null;
    title: string;
    description: string;
    type: string;
    status: string;
    started_at: string;
    resolved_at: string | null;
    node?: { name: string };
}

export interface IspNetworkProfile {
    id: number;
    name: string;
    download_limit: number;
    upload_limit: number;
    burst_limit: string | null;
    fup_limit_gb: number | null;
    fup_speed: string | null;
    fup_enabled: boolean;
    status: string;
}
