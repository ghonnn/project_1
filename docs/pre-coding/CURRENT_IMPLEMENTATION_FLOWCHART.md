# NEXBIL Current Implementation Flowchart

Status: after Laravel API MVP + Filament Admin Panel
Source: PRD v3.4 Router-Centric, pre-coding blueprint, and implemented MVP.

## Current System Access Flow

```mermaid
flowchart TD
    A[Admin opens /admin] --> B[Filament Login]
    B --> C{User active and platform_owner?}
    C -- No --> D[Access denied]
    C -- Yes --> E[Admin Dashboard]
    E --> F[Manage Tenant]
    E --> G[Manage Catalog]
    E --> H[Manage CRM]
    E --> I[Manage OSS Router]
    E --> J[Manage RADIUS]
    E --> K[Manage Billing]
    E --> L[Read Audit Logs]
```

## Router-Centric OSS/BSS Flow

```mermaid
flowchart TD
    T[Tenant] --> C[Customer]
    T --> SC[Service Category]
    SC --> P[Product]
    C --> S[Service]
    P --> S

    T --> R[Router]
    R --> RI[Router Interface]
    R --> NAS[Radius NAS]

    S --> M[Service Router Mapping]
    M --> R
    M --> RI
    M --> CM[Customer Router Mapping]

    S --> RU[Radius User]
    RU --> RP[Radius Profile]
    RU --> R
    RU --> FR[FreeRadius]

    S --> INV[Invoice]
    INV --> PAY[Payment]
```

## Service Activation Flow

```mermaid
flowchart TD
    A[Create Customer] --> B[Select Product / Category]
    B --> C[Create Service]
    C --> D{Category requires router mapping?}
    D -- No --> H[Activate Service]
    D -- Yes --> E[Create Router + Interface]
    E --> F[Map Service to Router / Interface]
    F --> G{Category requires RADIUS?}
    G -- No --> H
    G -- Yes --> I[Create Radius User + Profile]
    I --> J[Sync / Test RADIUS]
    J --> H
    H --> K[Billing Eligible]
```

## Billing, Suspend, Unsuspend Flow

```mermaid
flowchart TD
    A[Active Service] --> B[Generate Invoice]
    B --> C[Deliver Invoice]
    C --> D{Payment received?}
    D -- Yes --> E[Record Payment]
    E --> F[Invoice Paid / Partial]
    F --> G{Service suspended?}
    G -- Yes --> H[Unsuspend Service]
    H --> I[Reactivate Radius User]
    G -- No --> J[Keep Active]
    D -- No / Overdue --> K[Dunning Policy]
    K --> L[Suspend Service]
    L --> M[Suspend Radius User]
```

## Router Down Impact Flow

```mermaid
flowchart TD
    A[Router Down / SNMP Failed] --> B[Find Router Interfaces]
    B --> C[Find Service Router Mapping]
    C --> D[Find Customers]
    C --> E[Find Radius Users]
    D --> F[Calculate Customer Count]
    E --> G[Calculate Radius Impact]
    C --> H[Calculate Revenue Impact]
    F --> I[Create Incident / Ticket]
    G --> I
    H --> I
    I --> J[Work Order if Field Action Needed]
```

## Next Build Priority

```mermaid
flowchart LR
    A[Stabilize Filament MVP] --> B[Add Role / Tenant Admin Access]
    B --> C[Add Service Router Mapping UI]
    C --> D[Add Router Script Generator UI]
    D --> E[Add NAS Device UI]
    E --> F[Add Billing Item UI]
    F --> G[Add Monitoring / Impact MVP]
```

## Immediate Manual Test Checklist

- Login to `/admin`.
- Open `Tenants`, verify `NEX Demo ISP`.
- Open `Routers`, edit seed router and set role `PPPoE Router`.
- Open `Radius Servers`, verify FreeRadius host.
- Open `Customers`, create one test customer.
- Open `Services`, create one service for that customer.
- Use API for service-router mapping until the Filament mapping resource is added.
- Create Radius User.
- Create Invoice and Payment.
- Check `Audit Logs`.
