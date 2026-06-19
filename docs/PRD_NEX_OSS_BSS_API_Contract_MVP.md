# PRD NEX OSS/BSS - API Contract MVP v3.4 Router-Centric

Base path: `/api/v1`

Auth: Bearer token. Tenant-scoped request menggunakan tenant dari token atau path `{tenant_id}`. Semua endpoint harus menegakkan tenant isolation.

## Gap Analysis v3.4

| Area | Gap Lama | Revisi v3.4 |
|---|---|---|
| Router endpoint | Belum tersedia | Ditambahkan `/routers`, `/router-interfaces`, `/router-links` |
| Impact/capacity/SNMP | Belum tersedia | Ditambahkan endpoint impact, capacity, SNMP |
| Script generator | Belum tersedia | Ditambahkan `POST /router-script-generator` |
| Radius mapping | Radius user belum eksplisit ke router | Request/response radius user memuat `router_id` |

## Common Errors

- 400: validation error
- 401: missing/invalid token
- 403: insufficient permission
- 404: resource not found
- 409: business conflict
- 500: server error

## Auth

### POST /auth/login

Request:

```json
{"username":"string","password":"string"}
```

Response:

```json
{"access_token":"string","token_type":"bearer","expires_in":3600,"refresh_token":"string"}
```

## Customer

### GET /tenants/{tenant_id}/customers

Permission: Tenant Owner, Tenant Admin, Sales, NOC restricted.

### POST /tenants/{tenant_id}/customers

Permission: Tenant Owner, Tenant Admin, Sales, Partner restricted.

### GET /tenants/{tenant_id}/customers/{customer_id}

Response includes service and router mapping summary for authorized internal roles.

## Service

### POST /tenants/{tenant_id}/services

Request:

```json
{
  "customer_id": "uuid",
  "product_id": "uuid",
  "contract_id": "uuid",
  "service_category_id": "uuid",
  "cid": "CID-001",
  "metadata": {}
}
```

Validation:

- Customer, product, contract, and service category must belong to tenant.
- Signed/active contract is required for recurring billing.
- If service category requires router mapping, mapping must exist before activation.

### PATCH /tenants/{tenant_id}/services/{service_id}

Allowed operations: activate, suspend, unsuspend, terminate, update metadata.

## Router Management

### GET /tenants/{tenant_id}/routers

Query params:

- `role`
- `status`
- `snmp_status`
- `q`
- `limit`
- `offset`

Permission: Platform Owner, Tenant Owner, Tenant Admin, NOC, Teknisi read-only.

Response model: `RouterStatus[]`.

### POST /tenants/{tenant_id}/routers

Request:

```json
{
  "router_name": "BNG Jakarta 01",
  "hostname": "bng-jkt-01",
  "vendor": "MikroTik",
  "model": "CCR",
  "serial_number": "string",
  "router_role": "bng",
  "site_name": "Jakarta POP",
  "management_ip": "10.0.0.1",
  "latitude": -6.200000,
  "longitude": 106.816666,
  "snmp_profile": {}
}
```

Validation:

- `router_role` must be one of Core Router, Aggregation Router, Edge Router, PPPoE Router, BNG, Wireless Gateway, POP Router, BTS Router.
- Hostname unique per tenant.

### PUT /tenants/{tenant_id}/routers/{id}

Updates router identity, role, SNMP profile, status, or location.

### GET /tenants/{tenant_id}/routers/{id}/impact

Response model: `RouterImpact`.

Returns customer count, service count, radius user count, revenue impact, and incident recommendation.

### GET /tenants/{tenant_id}/routers/{id}/capacity

Response model: `RouterCapacity`.

Returns CPU, memory, traffic, utilization history, capacity warning, and capacity critical status.

### GET /tenants/{tenant_id}/routers/{id}/snmp

Response model: `SNMPStatus`.

Returns `Reachable`, `Unreachable`, `Auth Failed`, or `Not Configured`.

## Router Interface

### GET /tenants/{tenant_id}/router-interfaces

Query params: `router_id`, `status`, `interface_type`.

### POST /tenants/{tenant_id}/router-interfaces

Request:

```json
{
  "router_id": "uuid",
  "interface_name": "ether1",
  "interface_type": "ethernet",
  "ip_address": "10.0.0.2",
  "vlan_id": 100,
  "speed_mbps": 1000
}
```

## Router Link

### GET /tenants/{tenant_id}/router-links

Query params: `router_id`, `status`, `link_type`.

### POST /tenants/{tenant_id}/router-links

Request:

```json
{
  "router_a_id": "uuid",
  "router_b_id": "uuid",
  "interface_a_id": "uuid",
  "interface_b_id": "uuid",
  "link_type": "backbone",
  "capacity_mbps": 10000
}
```

## Service Router Mapping

### POST /tenants/{tenant_id}/services/{service_id}/router-mapping

Request:

```json
{
  "router_id": "uuid",
  "interface_id": "uuid",
  "vlan_id": 200,
  "is_primary": true
}
```

Validation:

- Required for Internet/network services.
- Optional for non-network services.
- Router and interface must belong to tenant.

## Radius

### POST /tenants/{tenant_id}/radius/profiles

Creates Radius profile.

### POST /tenants/{tenant_id}/radius/users

Request:

```json
{
  "customer_id": "uuid",
  "service_id": "uuid",
  "router_id": "uuid",
  "profile_id": "uuid",
  "username": "string",
  "secret": "string"
}
```

Validation:

- Username unique per tenant.
- Service must belong to customer and tenant.
- Router is required if service category requires router mapping.

### POST /tenants/{tenant_id}/nas

Request:

```json
{
  "router_id": "uuid",
  "hostname": "bng-jkt-01",
  "nas_ip_address": "10.0.0.1",
  "vendor_type": "mikrotik",
  "secret": "encrypted-or-plain-input"
}
```

## Router Script Generator

### POST /tenants/{tenant_id}/router-script-generator

Request:

```json
{
  "router_id": "uuid",
  "os_version": "ROS7",
  "script_type": "PPPoE",
  "radius_server_ip": "10.10.10.10",
  "radius_secret": "string",
  "service_profile": "100M"
}
```

Response model: `GeneratedScript`.

Supported output:

- ROS6 PPPoE
- ROS7 PPPoE
- ROS6 Hotspot
- ROS7 Hotspot

## Response Models

### RouterStatus

```json
{
  "id": "uuid",
  "router_name": "string",
  "hostname": "string",
  "router_role": "bng",
  "status": "active",
  "snmp_status": "Reachable",
  "management_ip": "10.0.0.1"
}
```

### RouterCapacity

```json
{
  "router_id": "uuid",
  "cpu_percent": 40.5,
  "memory_percent": 62.1,
  "traffic_in_bps": 1000000,
  "traffic_out_bps": 2000000,
  "utilization_percent": 70.2,
  "level": "normal|warning|critical"
}
```

### RouterImpact

```json
{
  "router_id": "uuid",
  "affected_customers": 120,
  "affected_services": 126,
  "affected_radius_users": 118,
  "revenue_impact_monthly": 25000000,
  "incident_severity": "P1"
}
```

### SNMPStatus

```json
{
  "router_id": "uuid",
  "status": "Reachable",
  "last_checked_at": "2026-06-18T00:00:00Z",
  "latency_ms": 12
}
```

### GeneratedScript

```json
{
  "router_id": "uuid",
  "os_version": "ROS7",
  "script_type": "PPPoE",
  "script": "/radius add ...",
  "warnings": []
}
```

## Billing, Payment, Ticket, Work Order

Existing MVP endpoints remain valid with these additions:

- Invoice generation validates service active state.
- Suspend triggers Radius user suspended.
- Ticket can reference `router_id`, `interface_id`, and `incident_id`.
- Work order can reference `router_id`, `interface_id`, and `router_link_id`.

## QC Checklist

- Endpoint wajib ada: `GET /routers`, `POST /routers`, `PUT /routers/{id}`.
- Endpoint wajib ada: `GET /router-interfaces`, `POST /router-interfaces`.
- Endpoint wajib ada: `GET /router-links`, `POST /router-links`.
- Endpoint wajib ada: `GET /routers/{id}/impact`, `GET /routers/{id}/capacity`, `GET /routers/{id}/snmp`.
- Endpoint wajib ada: `POST /router-script-generator`.
- Response model wajib ada: `RouterStatus`, `RouterCapacity`, `RouterImpact`, `SNMPStatus`, `GeneratedScript`.
