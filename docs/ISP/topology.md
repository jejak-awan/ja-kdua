# JANET System Topology

![JANET System Topology](./topology.png)

This document describes the network architecture and data flow between the central management system (JANET), Radius server, and various MikroTik router deployment modes.

## Topology Diagram

```mermaid
graph TD
    subgraph "Central Infrastructure (Cloud)"
        CMS["JANET App (Aplikasi Kita)"]
        Radius["Radius Server (AAA)"]
        VPN_Srv["VPN Gateway (Server VPN)"]
        DB[(PostgreSQL Database)]
    end

    subgraph "ISP Edge Nodes (MikroTik)"
        R1["Router 1 (Public IP)"]
        R2["Router 2 (VPN Mode)"]
        RX["Router X (VPN Mode)"]
    end

    %% Sync & AAA
    CMS -- "NAS Sync" --> Radius
    Radius -- "Data Check" --> DB
    
    %% Connections
    R1 -- "AAA (UDP 1812/1813)" --> Radius
    R2 -- "OVPN/SSTP Tunnel" --> VPN_Srv
    RX -- "OVPN/SSTP Tunnel" --> VPN_Srv
    
    %% Internal Routing
    VPN_Srv -- "Internal AAA Flow" --> Radius
    CMS -- "Management (via Tunnel)" --> R2
    CMS -- "Management (via Tunnel)" --> RX
    
    %% Direct Management
    CMS -- "Management (Direct IP)" --> R1
```

## Description

1.  **JANET App**: The central brain of the system. It handles router management, NAS synchronization to the Radius server, and real-time monitoring via API.
2.  **Radius Server**: Handles AAA (Authentication, Authorization, and Accounting) for PPPoE and Hotspot users. It queries the JANET database to validate credentials.
3.  **VPN Gateway**: A management tunnel server for routers that do not have a public IP. It allows bidirectional communication between the central JANET and private edge nodes.
4.  **Static Mode (R1)**: Routers with a direct Public IP. They communicate directly with the Radius server and CMS.
5.  **VPN Mode (R2/RX)**: Routers behind NAT or private IPs. They use OVPN/SSTP/L2TP to connect to the central VPN server, enabling remote management and Radius AAA flow.
