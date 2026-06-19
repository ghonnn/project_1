# PRD NEX OSS/BSS - Event Driven Architecture v3.4 Router-Centric

Peran: Solution Architect OSS/BSS ISP

Dokumen ini merevisi Step 6 agar event system mendukung PRD v3.4 Router-Centric.

## Gap Analysis v3.4

| Area | Gap Lama | Revisi v3.4 |
|---|---|---|
| Router events | Belum ada event router lifecycle | Ditambahkan `router.created`, `router.updated`, `router.deleted` |
| Monitoring | Belum ada router up/down/capacity/SNMP | Ditambahkan event monitoring router |
| Mapping | Service mapping belum event-driven | Ditambahkan `service.router.mapped` |
| Impact | Customer impact belum otomatis | Ditambahkan `customer.impact.detected` dan `incident.created` |

## Event Envelope

Semua event menggunakan envelope berikut:

```json
{
  "event_id": "uuid",
  "event_name": "router.down",
  "schema_version": "1.0",
  "tenant_id": "uuid",
  "occurred_at": "2026-06-18T00:00:00Z",
  "trace_id": "uuid",
  "producer": "router-service",
  "payload": {}
}
```

## Event Table

| Event | Producer | Consumer | Payload Utama | Retry Strategy |
|---|---|---|---|---|
| `customer.created` | Customer Service | Billing, CRM, Audit | `customer_id`, `tenant_id`, `status` | 3 retries, DLQ |
| `customer.updated` | Customer Service | CRM, Audit, Impact Cache | `customer_id`, changed fields | 3 retries, DLQ |
| `service.created` | Service Service | Provisioning, Billing, Audit | `service_id`, `customer_id`, `category_id` | 3 retries, DLQ |
| `service.activated` | Service Service | Billing, Radius, Notification, Audit | `service_id`, `customer_id`, `requires_radius` | 5 retries, DLQ |
| `service.suspended` | Service Service | Radius, Notification, Audit | `service_id`, `reason`, `invoice_id` | 5 retries, DLQ |
| `service.unsuspended` | Service Service | Radius, Notification, Audit | `service_id`, `reason`, `payment_id` | 5 retries, DLQ |
| `service.router.mapped` | Provisioning Service | Router, Radius, Billing, Audit | `service_id`, `router_id`, `interface_id`, `vlan_id` | 5 retries, DLQ |
| `router.created` | Router Service | NOC, GIS, Audit | `router_id`, `router_role`, `hostname`, `status` | 3 retries, DLQ |
| `router.updated` | Router Service | NOC, GIS, Impact Cache, Audit | `router_id`, changed fields | 3 retries, DLQ |
| `router.deleted` | Router Service | NOC, GIS, Audit | `router_id`, `deleted_at` | 3 retries, DLQ |
| `router.up` | Monitoring Service | NOC, Incident, Notification, Audit | `router_id`, `snmp_status`, `recovered_at` | 5 retries, DLQ |
| `router.down` | Monitoring Service | Impact Analysis, NOC, Incident, Notification | `router_id`, `detected_at`, `interface_id` | 10 retries, DLQ, alert on fail |
| `router.capacity.warning` | Capacity Service | NOC, Reporting, Notification | `router_id`, `utilization_percent`, metrics | 5 retries, DLQ |
| `router.capacity.critical` | Capacity Service | NOC, Incident, Notification | `router_id`, `utilization_percent`, metrics | 10 retries, DLQ, alert on fail |
| `router.snmp.failed` | Monitoring Service | NOC, Router Service, Audit | `router_id`, `snmp_status`, error | 5 retries, DLQ |
| `radius.user.created` | Radius Service | FreeRadius Sync, Audit | `radius_user_id`, `service_id`, `router_id` | 5 retries, DLQ |
| `radius.user.suspended` | Radius Service | FreeRadius Sync, Audit | `radius_user_id`, `service_id`, reason | 5 retries, DLQ |
| `radius.user.activated` | Radius Service | FreeRadius Sync, Audit | `radius_user_id`, `service_id` | 5 retries, DLQ |
| `customer.impact.detected` | Impact Analysis Service | Incident, NOC, Notification, Reporting | `router_id`, customer/service/radius counts, revenue impact | 10 retries, DLQ |
| `incident.created` | Incident Service | Ticket, Work Order, Notification, Audit | `incident_id`, `severity`, `router_id`, impact summary | 5 retries, DLQ |
| `invoice.created` | Billing Service | Notification, Payment, Audit | `invoice_id`, `customer_id`, amount | 3 retries, DLQ |
| `invoice.paid` | Payment Service | Billing, Service, Radius, Notification | `invoice_id`, `payment_id`, `customer_id` | 5 retries, DLQ |
| `payment.reconciled` | Payment Service | Billing, Service, Radius, Audit | `payment_id`, `invoice_id`, amount | 5 retries, DLQ |
| `ticket.created` | Ticket Service | NOC, Work Order, Notification, Audit | `ticket_id`, `customer_id`, `router_id` | 3 retries, DLQ |
| `work_order.created` | Work Order Service | Technician, Notification, Audit | `work_order_id`, `router_id`, `service_id` | 3 retries, DLQ |

## Router Down Cascade

```text
router.down
  -> Impact Analysis Service
  -> customer.impact.detected
  -> Incident Service
  -> incident.created
  -> Ticket Service / Work Order Service / Notification Service
```

Payload `customer.impact.detected`:

```json
{
  "router_id": "uuid",
  "affected_customer_count": 120,
  "affected_service_count": 126,
  "affected_radius_user_count": 118,
  "revenue_impact_monthly": 25000000,
  "severity": "P1"
}
```

## Service Activation Cascade

```text
service.created
  -> service.router.mapped
  -> radius.user.created
  -> service.activated
  -> invoice schedule active
```

Rules:

- `service.router.mapped` wajib sebelum activation untuk layanan Internet.
- `radius.user.created` wajib untuk service dengan `requires_radius = true`.
- Billing recurring hanya aktif setelah service activation valid.

## Capacity Warning Cascade

```text
router.capacity.warning
  -> NOC Dashboard
  -> Capacity Dashboard
  -> optional ticket
```

```text
router.capacity.critical
  -> Incident Service
  -> NOC escalation
  -> optional Work Order
```

## Topic Naming

- `customer.events`
- `service.events`
- `router.events`
- `router.monitoring.events`
- `radius.events`
- `billing.events`
- `payment.events`
- `incident.events`
- `ticket.events`
- `work_order.events`

## Retry and DLQ Rules

- Idempotency key: `event_id`.
- Consumers must be idempotent by target entity and event version.
- Retry uses exponential backoff.
- DLQ must preserve full envelope and error message.
- Monitoring events that fail after retry must alert NOC.

## QC Checklist

- Event wajib ada: `router.created`, `router.updated`, `router.deleted`.
- Event wajib ada: `router.up`, `router.down`.
- Event wajib ada: `router.capacity.warning`, `router.capacity.critical`.
- Event wajib ada: `router.snmp.failed`.
- Event wajib ada: `service.router.mapped`.
- Event wajib ada: `radius.user.created`.
- Event wajib ada: `customer.impact.detected`.
- Event wajib ada: `incident.created`.
- Setiap event menampilkan producer, consumer, payload, dan retry strategy.
