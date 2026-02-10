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
    partner_id?: number;
    router_id?: number;
    server_id?: number;
    service_category?: string;
    billing_due_date?: string;
    billing_notes?: string;
    is_taxed?: boolean;
    unique_code?: number;
    address_list?: string;
    plan?: IspPlan;
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

export interface IspPlan {
    id: number;
    name: string;
    type: 'fiber' | 'hotspot';

    // Bandwidth
    download_limit?: string;
    upload_limit?: string;
    burst_limit?: string;

    // Network
    mikrotik_group?: string;
    mikrotik_rate_limit?: string;

    // Business
    price: number;
    cost_price: number;
    commission: number;
    partner_price?: number;
    mikrotik_profile?: string;

    // Policy
    shared_users: number;
    active_period: number;
    quota_limit_mb?: number;

    // FUP
    fup_limit_gb?: number;
    fup_speed?: string;
    fup_enabled: boolean;

    // UI & Status
    color?: string;
    description?: string;
    status: string;
    features?: string[] | null;

    // Legacy mapping
    speed_limit?: string;
    is_active?: boolean;
}

// Aliases for legacy support during migration
export type BillingPlan = IspPlan;
export type IspNetworkProfile = IspPlan;
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


export interface Voucher {
    id: number;
    code: string;
    profile_id: number | null;
    batch_id: number | null;
    status: 'available' | 'sold' | 'used' | 'expired' | 'disabled';
    sold_at: string | null;
    used_at: string | null;
    partner_id: number | null;
    customer_id: number | null;
    price: number;
    partner_price: number | null;
    partner?: Partner;
    profile?: IspPlan;
    batch?: VoucherBatch;
}

export interface VoucherBatch {
    id: number;
    name: string;
    partner_id: number | null;
    quantity: number;
    prefix: string | null;
    created_at: string;
    profile?: IspPlan;
}

export interface Partner {
    id: number;
    user_id: number;
    name: string;
    phone: string | null;
    address: string | null;
    category: 'reseller' | 'biller';
    saldo: number;
    limit_hutang: number;
    commission_rate: number;
    status: 'active' | 'inactive';
    created_at: string;
    user?: { id: number; name: string; email: string };
    transactions?: Transaction[];
}

export interface Transaction {
    id: number;
    parent_type: string;
    parent_id: number;
    type: 'credit' | 'debit';
    amount: number;
    saldo_before: number;
    saldo_after: number;
    category: string;
    description: string | null;
    reference_type: string | null;
    reference_id: number | null;
    created_by: number | null;
    created_at: string;
    creator?: { id: number; name: string };
}

export interface Contract {
    id: number;
    customer_id: number;
    start_date: string;
    end_date: string;
    terms: string | null;
    status: 'active' | 'expired' | 'terminated';
    created_at: string;
    customer?: Customer;
}

export interface Coupon {
    id: number;
    code: string;
    type: 'percentage' | 'fixed';
    value: number;
    max_uses: number | null;
    used_count: number;
    min_purchase: number | null;
    applicable_plans: number[] | null;
    starts_at: string | null;
    expires_at: string | null;
    is_active: boolean;
    created_at: string;
}

export interface CouponUsage {
    id: number;
    coupon_id: number;
    customer_id: number;
    invoice_id: number | null;
    discount_amount: number;
    created_at: string;
}

export interface Inventory {
    id: number;
    name: string;
    sku: string | null;
    category: string | null;
    quantity: number;
    min_stock: number;
    unit: string;
    cost_price: number | null;
    sell_price: number | null;
    status: string;
    created_at: string;
}

export interface InventoryTransaction {
    id: number;
    inventory_id: number;
    type: 'in' | 'out' | 'adjustment';
    quantity: number;
    customer_id: number | null;
    user_id: number | null;
    notes: string | null;
    created_at: string;
}

export interface CustomerDevice {
    id: number;
    customer_id: number;
    node_id: number | null;
    type: string;
    serial_number: string;
    mac_address: string | null;
    status: string;
    activated_at: string | null;
    expiration_date: string | null;
    metadata?: Record<string, unknown>;
}

// Deprecated or removed: CustomerTransaction is now part of Transaction

export interface IpPool {
    id: number;
    name: string;
    network: string;
    gateway: string | null;
    dns_primary: string | null;
    dns_secondary: string | null;
    vlan_id: number | null;
    router_id: number | null;
    status: string;
    description: string | null;
    router?: NetworkNode;
    usage_stats?: {
        total: number;
        available: number;
        assigned: number;
        reserved: number;
        usage_percent: number;
    };
}

export interface IpPoolAddress {
    id: number;
    pool_id: number;
    ip_address: string;
    customer_id: number | null;
    status: 'available' | 'assigned' | 'reserved';
    assigned_at: string | null;
    notes: string | null;
    customer?: Customer;
}

export interface PrintTemplate {
    id: number;
    name: string;
    type: 'invoice' | 'voucher' | 'receipt' | 'contract';
    content: string;
    page_size: string;
    orientation: string;
    is_default: boolean;
    created_at: string;
}

export interface WaTemplate {
    id: number;
    name: string;
    content: string;
    variables: string[];
    created_at: string;
}

export interface WaBlast {
    id: number;
    name: string;
    template_id: number | null;
    message: string;
    target_filter: Record<string, unknown>;
    status: 'draft' | 'sending' | 'sent' | 'failed';
    sent_count: number;
    failed_count: number;
    created_at: string;
}

export interface WaSchedule {
    id: number;
    name: string;
    event: string;
    template_id: number;
    delay_hours: number;
    is_active: boolean;
    created_at: string;
}
