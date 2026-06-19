# NEX OSS/BSS ISP Cloud Platform - Code-Aligned PRD Update

Status: practical update after reviewing current GitHub implementation  
Date: 2026-06-20  
Scope: align the current Laravel API MVP and Filament Admin Panel with PRD v3.4 Router-Centric and the attached flowchart.

## 1. Executive Decision

The current codebase is already pointed in the right direction: it is a Laravel OSS/BSS MVP with a router-centric data model, tenant-aware API routes, Filament admin resources, RADIUS primitives, invoice/payment primitives, service-router mapping, and a RouterOS script generator.

The next update should **not** broaden the product into all PRD modules at once. The best practical path is to close the gaps that block a usable ISP operational MVP:

1. Finish tenant-safe operational CRUD.
2. Finish service activation guardrails.
3. Turn router/RADIUS/billing flows into a clean end-to-end workflow.
4. Add missing PRD entities only when they are needed by the workflow.
5. Keep POP and BTS as `router_role` values only.

## 2. Locked Product Rule

```text
Customer -> Service -> Router -> Router Interface -> Radius NAS -> FreeRadius
```

Rules:

- Customer must never map directly to POP or BTS.
- POP and BTS remain only `routers.router_role` values: `pop_router` and `bts_router`.
- Internet/network service activation requires router mapping when `service_categories.requires_router_mapping = true`.
- RADIUS user requires router linkage when `service_categories.requires_radius = true`.
- Billing should only invoice active services.
- Suspend/unsuspend operates at service level first, then syncs Radius user status.

## 3. Current Implementation Snapshot

### Implemented and usable now

| Area | Current state | Practical note |
|---|---|---|
| Laravel API MVP | API routes under `/api/v1` with auth and tenant prefix | Keep as backend contract for admin and future portal/mobile. |
| Tenant structure | `tenants`, `users`, roles/permissions base tables | Needs stronger tenant admin access and permission matrix. |
| Customer/service/catalog | `customers`, `service_categories`, `products`, `services` | Service category flags are already present and should drive validation. |
| Router topology | `routers`, `router_interfaces`, `router_links`, `service_router_mapping`, `customer_router_mapping` | Correct foundation for router-centric PRD. |
| RADIUS | `radius_servers`, `radius_profiles`, `radius_users`, `radius_sync_logs` | NAS table exists, but API/UI flow needs completion. |
| Billing MVP | `invoices`, `invoice_items`, `payments` | Current implementation allows active-service invoicing and manual reconciliation. |
| Suspend/unsuspend | Service action updates service and Radius user status | Good MVP. Add policy automation later. |
| Router script generator | API and Filament page exist | Convert hardcoded script generation into `router_script_templates`. |
| Audit logs | Critical operations log activity | Expand to all write operations and sync events. |

### Important gap vs PRD v3.4

| Gap | Current condition | Recommended update |
|---|---|---|
| Contract/e-signature | PRD requires contract before recurring billing; app schema does not yet implement contract table in Laravel MVP | Add `contracts` and `contract_documents` before advanced billing. For MVP, allow manual contract reference in service metadata. |
| NAS Management | `nas_devices` table exists; route/resource flow is incomplete | Add API + Filament `NasDeviceResource` and enforce router linkage. |
| Router script template | PRD asks template engine; current generator builds script inline | Add `router_script_templates` and render script from template variables. |
| Router capacity history | PRD requires capacity dashboard; Laravel MVP migration does not include capacity history | Add `router_capacity_history` migration and lightweight dashboard query. |
| NOC impact analysis | Flow exists in docs, but no endpoint yet | Add `/routers/{id}/impact` endpoint before complex monitoring integration. |
| Work order/ticket detail | Base tables exist, but lifecycle fields are thin | Add priority, category, SLA timestamps, assignee, verification fields. |
| Billing automation | Manual invoice/payment works; recurring/prorate not yet full | Keep manual first, then add billing cycle job after service lifecycle is stable. |
| Tenant isolation hardening | Tenant middleware exists by route shape; needs test coverage and policy guards | Add feature tests for cross-tenant read/write denial. |

## 4. Updated MVP Scope

### In scope for next practical release

1. Tenant-safe Admin MVP.
2. Customer, service category, product, service CRUD.
3. Router, interface, link, service-router mapping CRUD.
4. RADIUS server, profile, NAS device, Radius user CRUD.
5. Service activate/suspend/unsuspend guardrails.
6. RouterOS RADIUS script generator using templates.
7. Manual invoice + payment reconciliation.
8. Router impact endpoint and basic incident/ticket creation.
9. Audit log on all create/update/status/sync actions.
10. Updated documentation and flowchart as source of truth.

### Explicitly out of scope for next practical release

- Full MikroTik API control.
- Full OLT/ONU auto-provisioning.
- Marketplace.
- AI NOC automation.
- Multi-country tax engine.
- Complex customer mobile app.
- Full workflow designer.

## 5. Updated Domain Requirements

### 5.1 Tenant and RBAC

Requirements:

- Every operational row must have `tenant_id`, except global platform configuration.
- Platform Owner can see all tenants.
- Tenant Admin can see only own tenant data.
- Finance, NOC, Sales, Technician should be represented in role matrix even if enforcement starts simple.

Acceptance criteria:

- A user from Tenant A cannot fetch, create, update, map, invoice, or suspend records in Tenant B.
- API tests must cover cross-tenant denial for customers, services, routers, radius users, invoices, and payments.

### 5.2 Customer and Service Lifecycle

Updated flow:

```text
Lead/Customer Created
-> Product and Service Category selected
-> Service created as requested
-> Router mapping required check
-> Radius user required check
-> Service activation
-> Invoice eligibility
-> Suspend/unsuspend lifecycle
```

Activation rules:

- If `requires_router_mapping = true`, service cannot become active without `service_router_mapping`.
- If `requires_radius = true`, service cannot become active without `radius_users`.
- If Radius user is created for a network service, `router_id` is required.

### 5.3 Router Management

Required router roles:

- `core_router`
- `aggregation_router`
- `edge_router`
- `pppoe_router`
- `bng`
- `wireless_gateway`
- `pop_router`
- `bts_router`

Required next improvements:

- Add controlled validation to Filament and API consistently.
- Add interface uniqueness: `tenant_id + router_id + interface_name`.
- Add router impact endpoint.
- Add capacity snapshot table.

### 5.4 Radius and FreeRadius

Required flow:

```text
Radius Server
-> NAS Device mapped to Router
-> Radius Profile
-> Radius User mapped to Customer + Service + Router
-> Sync/Test FreeRadius
-> Activate/Suspend/Unsuspend with service state
```

Practical change:

- Do not use MikroTik API in early phase.
- Use FreeRadius as source of authentication truth.
- Store RADIUS/NAS secrets encrypted before production.
- Add sync logs for create, activate, suspend, and failure cases.

### 5.5 Router Script Generator

Current generator is useful but should be upgraded.

Required update:

- Add `router_script_templates` table.
- Store template by `vendor`, `os_version`, `script_type`, `status`.
- Render variables: router hostname, Radius host, auth/acct port, secret, service type, interim update.
- Output copyable script and downloadable `.rsc`.

Acceptance criteria:

- ROS6 PPPoE script generated from template.
- ROS7 PPPoE script generated from template.
- Hotspot script generated from template.
- No RADIUS secret written to audit log.

### 5.6 Billing MVP

Current manual invoice/payment is acceptable for MVP, but recurring automation must wait until service lifecycle is clean.

Updated billing rule:

```text
Active Service -> Invoice Draft/Issued -> Payment Reconciled -> Invoice Paid/Partial -> Unsuspend Evaluation
```

Acceptance criteria:

- Invoice cannot be created for non-active service.
- Payment increments paid amount.
- Paid amount equal or above total marks invoice `paid`.
- Partial amount marks invoice `partial_paid`.
- Later: payment paid should trigger unsuspend evaluation for related suspended services.

### 5.7 Ticket, Work Order, and NOC Impact

Add before advanced monitoring:

- `GET /routers/{id}/impact`
- affected service count
- affected customer count
- affected radius user count
- estimated MRR/revenue impact from active services/invoice item history
- active ticket count
- optional create incident/ticket from impact result

Updated flow:

```text
Router Offline/SNMP Failed
-> Find Service Router Mapping
-> Find Customers
-> Find Radius Users
-> Calculate Revenue Impact
-> Create Incident/Ticket
-> Create Work Order if field action needed
```

## 6. Database Delta Required

Add or adjust these in Laravel MVP:

```text
contracts
contract_documents
nas_devices API/resource completion
router_capacity_history
router_script_templates
incidents or ticket incident fields
work_order assignment and verification fields
```

Recommended migrations:

1. `add_contracts_for_billing_gate.php`
2. `add_router_capacity_history.php`
3. `add_router_script_templates.php`
4. `add_ticket_sla_and_incident_fields.php`
5. `add_work_order_assignment_fields.php`
6. `add_indexes_for_impact_queries.php`

Impact indexes:

```text
service_router_mapping(tenant_id, router_id)
service_router_mapping(tenant_id, service_id)
customer_router_mapping(tenant_id, router_id)
radius_users(tenant_id, router_id)
radius_users(tenant_id, service_id)
invoice_items(tenant_id, service_id)
routers(tenant_id, router_role, status)
```

## 7. API Delta Required

Add these endpoints before building heavy UI:

```text
GET    /api/v1/tenants/{tenant_id}/nas-devices
POST   /api/v1/tenants/{tenant_id}/nas-devices
GET    /api/v1/tenants/{tenant_id}/nas-devices/{id}
PUT    /api/v1/tenants/{tenant_id}/nas-devices/{id}

GET    /api/v1/tenants/{tenant_id}/routers/{id}/impact
POST   /api/v1/tenants/{tenant_id}/routers/{id}/impact/ticket

GET    /api/v1/tenants/{tenant_id}/router-script-templates
POST   /api/v1/tenants/{tenant_id}/router-script-templates
POST   /api/v1/tenants/{tenant_id}/router-script-generator

POST   /api/v1/tenants/{tenant_id}/invoices/{id}/evaluate-unsuspend
```

## 8. Filament Admin Delta Required

Add or finish these resources/pages:

- `NasDeviceResource`
- `ServiceRouterMappingResource` or relation manager under Service
- `RouterImpactPage`
- `RouterCapacityDashboard`
- `RouterScriptTemplateResource`
- improved `TicketResource`
- improved `WorkOrderResource`
- Tenant Admin role visibility restrictions

## 9. Roadmap Updated from Code Reality

| Phase | Practical name | Output |
|---|---|---|
| Phase 0 | Code/docs alignment | This PRD update and flowchart become source of truth. |
| Phase 1 | Tenant-safe core | Tenant, RBAC, customer, product, service category, service tests. |
| Phase 2 | Router/RADIUS operational MVP | Router, interface, mapping, NAS, Radius user, script template generator. |
| Phase 3 | Billing MVP hardening | Invoice active service only, payment reconciliation, paid/partial status, audit. |
| Phase 4 | Suspend/unsuspend automation | Dunning policy, Radius disable/enable, notification hooks. |
| Phase 5 | NOC impact MVP | Router impact endpoint, incident/ticket, basic work order. |
| Phase 6 | Capacity and monitoring | SNMP/LibreNMS/PRTG integration, capacity history, dashboard. |
| Phase 7 | Work Order and field service | Assignment, evidence, GPS, BA digital. |
| Phase 8 | SaaS/white label | License, usage metering, tenant branding. |
| Phase 9 | OSS advanced | OLT/ONU, IPAM, VLAN advanced, GIS. |
| Phase 10 | Marketplace and AI | Addons, anomaly, prediction. |

## 10. Immediate Development Backlog

### P0 - Must do next

- Add NAS Device API and Filament resource.
- Add router impact endpoint.
- Add router script templates table and migrate generator to template rendering.
- Add indexes for impact queries.
- Add tests for service activation guardrails.
- Add tests for cross-tenant isolation.

### P1 - High value after P0

- Add contract table and basic contract status gate.
- Add billing unsuspend evaluation.
- Add ticket SLA fields.
- Add work order assignment fields.
- Add capacity history snapshot table.

### P2 - Later

- Payment gateway callback.
- WhatsApp notification.
- Customer portal.
- Monitoring connector integration.
- GIS and inventory expansion.

## 11. Updated Acceptance Criteria

- Tenant A cannot access Tenant B data.
- Service requiring router mapping cannot activate without router/interface mapping.
- Service requiring Radius cannot activate without Radius user.
- Radius user for network service must reference router.
- NAS device must reference router.
- Invoice can only be created for active services.
- Payment reconciliation updates invoice status correctly.
- Service suspend disables/suspends related Radius users.
- Service unsuspend activates related Radius users.
- Router impact endpoint returns affected services, customers, Radius users, and revenue estimate.
- RouterOS script generator uses templates, not inline hardcoded script logic.
- Audit logs exist for customer, router, service status, Radius sync, script generation, invoice, and payment operations.
- POP/BTS are not created as separate modules or tables.

## 12. Practical Recommendation

Do not rebuild the repo. The current code already implements the correct skeleton. The best move is to **stabilize the implemented MVP and close the high-risk operational gaps** in this order:

```text
NAS Management
-> Service Router Mapping UI
-> Router Script Templates
-> Router Impact Endpoint
-> Billing Unsuspend Evaluation
-> Tenant Isolation Tests
-> Ticket/WO Operational Fields
-> Capacity History
```

This keeps the product aligned with PRD v3.4 while avoiding scope explosion.