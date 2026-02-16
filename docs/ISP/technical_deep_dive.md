# JANET ISP Technical Deep Dive

This document provides a comprehensive technical overview of the JA-CMS ISP module, covering topology, database schema, system integrations, and advanced operational features.

## 1. System Topology & Logic

The system follows a **Hub-and-Spoke** architecture with the JANET CMS as the central orchestrator.

### Connectivity Modes
- **Static (Direct)**: CMS communicates via Mikrotik API (port 8728) or SNMP (port 161) directly.
- **VPN Tunneled**: Routers establish OVPN/SSTP tunnels to a central VPN Gateway. The CMS manages these routers through their private tunnel IPs.

### Integration Flow
1. **Provisioning**: CMS -> `ProvisioningService` -> (RADIUS Sync + OLT ONU Registration).
2. **AAA**: Router -> Radius Server (UDP 1812/1813) -> CMS Database (PostgreSQL).
3. **CoA (Change of Authorization)**: CMS -> `RadiusIntegration` -> `radclient` (echo username | radclient) -> Router (UDP 1700).

---

## 2. Database Topology

The ISP system uses **PostgreSQL** and is built around several core entities and their relationships:

### Core Infrastructure
- `isp_service_nodes`: Unified table for all network hardware (Routers, OLTs, POPs).
- `olts`: Specific OLT details, synced from `isp_service_nodes` via Model observers.
- `odps`: Optical Distribution Points linked to OLT ports.
- `isp_outages`: Tracks network outage events with affected areas and duration.
- `isp_service_zones`: Geographic service zones for deployment coverage.

### Customer Management
- `isp_customers`: The heart of the module. Links to `users`, `isp_plans`, `isp_service_nodes` (Router), `olts`, and `odps`.
- `isp_customer_devices`: Stores ONT/ONU details (MAC, SN) for the customer.
- `isp_contracts`: Customer service contracts with SLA terms.
- `isp_partners`: Reseller/biller partners with balance and commission tracking.
- `isp_coupons` / `isp_coupon_usages`: Discount coupon system.

### IP Management (IPAM)
- `isp_subnets`: Defines prefix blocks assigned to nodes.
- `ip_pools`: Traditional Mikrotik-style pools for local allocation.
- `ip_pool_addresses`: Individual IP tracking.
- `isp_ip_addresses`: Granular IP assignment and DHCP lease management.

### Billing & Financial
- `isp_invoices` / `isp_invoice_items`: Monthly billing with line-item support.
- `isp_plans`: Subscription profiles (Prepaid/Postpaid) with Mikrotik rate limits.
- `isp_transactions`: Payment records linked to gateways (Midtrans/Xendit/Manual).
- `isp_vouchers` / `isp_voucher_batches`: Hotspot/PPPoE voucher system.
- `isp_payment_gateways`: Multi-gateway configuration.
- `isp_print_templates`: Customizable invoice/receipt templates.

### Operations
- `isp_technician_deployments`: Field technician dispatch tracking.
- `isp_service_requests`: Customer upgrade/downgrade/relocation requests.
- `isp_tickets`: Support ticket management.
- `isp_activity_logs`: Comprehensive audit trail.

### Third-Party Integrations
- `isp_whatsapp_templates/blasts/schedules`: WhatsApp Business notification system.

### Network Monitoring
- `isp_traffic_metrics`: Historical bandwidth usage data per interface.
- `isp_node_health_logs`: Node uptime, latency, CPU, and memory metrics.
- `isp_olt_signals`: ONU optical signal level history.
- `isp_olt_command_logs`: OLT CLI command audit trail.

---

## 3. Integration Patterns

### Mikrotik API
- Controlled via `RouterService` and `MikrotikService`.
- Uses `RouterOSAPI` library for structured communication.
- **Constraint**: High-frequency API polling can saturate Mikrotik CPU. Micro-caching is recommended.

### OLT Drivers (Strategy Pattern)
- `OltService` uses `OltDriver` interface. Drivers implement `registerONU`, `deregister`, `getSignal` methods.
- **Available Drivers**: `ZteOltDriver`, `HuaweiOltDriver`, `VsolOltDriver`, `GlobalOltDriver`, `HiosoOltDriver`, `HsqgOltDriver`, `ZymlinkOltDriver`, `MockOltDriver`.
- **Logic**: SSH-based CLI command execution via `BaseSshDriver`. Each driver translates vendor-specific CLI syntax.
- **ZTP Discovery**: `ZtpDiscoveryService` for auto-detecting unregistered ONUs.

### RADIUS (FreeRADIUS)
- Database-backed (PostgreSQL).
- Schema: Standard `radcheck`, `radreply`, `radacct`, `radpostauth`.
- **Sync**: Done via DB `updateOrInsert` on the `radius` connection.
- **HA Monitoring**: `RadiusStatService` tracks cluster node health and session counts.
- **CoA**: `CoAService` sends disconnect/update requests via UDP datagrams.

### Intelligent Dispatch
- `DispatchService` uses the **Haversine formula** for proximity-based technician ranking.
- Customer and technician coordinates stored on `isp_customers.latitude/longitude` and `users.latitude/longitude`.
- Returns distance-sorted technician list within configurable radius.

### Enterprise Reporting
- **Excel**: `InvoiceExport` (via `maatwebsite/excel`) for batch invoice data export.
- **PDF**: Network Health Certificates via `mpdf/mpdf` and Blade templates.

### BGP Toolkit
- `BgpToolkitService`: Looking glass, route analysis, and peering visibility tools.

---

## 4. Best Practices & Consistency (Reference: AGENT_GUIDE.md)

- **PHPStan Level 9**: Mandatory. No `mixed` allowed.
- **Model Observers**: Used for cross-model consistency (e.g., `ServiceNode` -> `Olt`).
- **Response Standard**: Always use `BaseApiController` success/error methods.
- **IP Sensitivity**: Use `IpHelper` for client IP detection.
- **Dark Mode**: Use `bg-background`, `bg-card`, `bg-popover` tokens only. Never raw `bg-white`.
- **i18n**: All user-facing strings must use `$t()` with keys from `resources/lang/{locale}/features/isp/`.
- **Frontend**: Shadcn UI components (Radix Vue), TanStack tables, Lucide icons. Composition API + TypeScript only.
