# NEX OSS/BSS v3.4 - Sprint Task Breakdown per Phase

Dokumen ini memecah roadmap Phase 0 sampai Phase 10 menjadi backlog sprint sebelum coding dimulai.

Asumsi:

- Sprint durasi 2 minggu.
- Team awal: Backend Laravel, Frontend, QA, DevOps, Product/BA.
- Phase 0-4 adalah critical path MVP.
- POP dan BTS tidak menjadi modul terpisah; gunakan Router Role.

## Phase 0 - Discovery & Blueprint

### Sprint 0.1 - Scope Lock

Deliverables:

- Final PRD v3.4 Router-Centric.
- Final business flow Step 1.
- Final acceptance criteria MVP.
- Decision log arsitektur.

Tasks:

- Lock rule `Customer -> Service -> Router -> Router Interface -> Radius NAS -> FreeRadius`.
- Validasi service category flags.
- Validasi role matrix.
- Validasi roadmap phase.

Exit Criteria:

- Product, engineering, dan stakeholder menyetujui scope MVP.

### Sprint 0.2 - Technical Contract

Deliverables:

- `api/openapi.yaml`.
- `database/migrations/0001_initial_router_centric_schema.sql`.
- `database/seeds/0001_core_seed.sql`.
- API standard dan test scenario.

Tasks:

- Review OpenAPI endpoint.
- Review schema dan FK.
- Review seed tenant/role/permission/product.
- Review tenant isolation test.

Exit Criteria:

- Backend dapat mulai scaffold Laravel tanpa menebak kontrak.

## Phase 1 - Core Foundation

### Sprint 1.1 - Laravel Foundation

Tasks:

- Scaffold Laravel API app.
- Configure PostgreSQL, Redis, queue.
- Setup Docker Compose.
- Setup Pint, PHPStan/Larastan, PHPUnit/Pest.
- Implement health check.

Acceptance:

- `/health` berjalan.
- CI lokal bisa menjalankan lint dan test.

### Sprint 1.2 - Auth, Tenant, RBAC

Tasks:

- Implement users, tenants, roles, permissions.
- Implement login, refresh, logout.
- Implement tenant context middleware.
- Implement permission middleware.

Acceptance:

- Token memiliki tenant dan role.
- User tenant tidak bisa mengakses tenant lain.

### Sprint 1.3 - Customer, Product, Service Category

Tasks:

- Implement customer CRUD.
- Implement service category read.
- Implement product CRUD.
- Seed core data.

Acceptance:

- Customer dan product tenant-scoped.
- Service category flags tersedia.

### Sprint 1.4 - Service Lifecycle Base

Tasks:

- Implement service CRUD.
- Implement status `requested`, `provisioning`, `active`, `suspended`, `terminated`, `failed`.
- Implement activation guard.

Acceptance:

- Service Internet belum bisa aktif tanpa router mapping.

## Phase 2 - Billing MVP

### Sprint 2.1 - Invoice Core

Tasks:

- Implement invoice and invoice item.
- Generate invoice number per tenant.
- Validate active service.
- Implement invoice list/detail.

Acceptance:

- Invoice tidak dapat dibuat untuk service invalid.

### Sprint 2.2 - Payment Manual

Tasks:

- Implement payment record.
- Implement payment reconciliation.
- Update invoice paid amount and status.

Acceptance:

- Payment reconciled mengubah invoice menjadi paid/partial paid.

### Sprint 2.3 - AR Aging and Dunning Trigger

Tasks:

- Implement overdue job.
- Implement AR aging query.
- Emit overdue event.

Acceptance:

- Invoice lewat due date menjadi overdue.

## Phase 3 - Payment & Notification

### Sprint 3.1 - Payment Gateway Adapter

Tasks:

- Define gateway interface.
- Implement sandbox adapter.
- Implement webhook verification.

Acceptance:

- Webhook idempotent.

### Sprint 3.2 - Notification

Tasks:

- Implement notification template.
- Implement WhatsApp/email adapter interface.
- Send invoice, overdue, paid, suspend notification.

Acceptance:

- Notification log tercatat per tenant.

## Phase 4 - Router Management FreeRadius AAA Suspend Engine

### Sprint 4.1 - Router CRUD

Tasks:

- Implement router CRUD.
- Implement router role enum.
- Implement SNMP status fields.
- Implement audit log for router changes.

Acceptance:

- Router role mendukung Core, Aggregation, Edge, PPPoE, BNG, Wireless Gateway, POP Router, BTS Router.

### Sprint 4.2 - Interface and Link

Tasks:

- Implement router interface CRUD.
- Implement router link CRUD.
- Validate interface belongs to router tenant.

Acceptance:

- Router link tidak bisa menghubungkan router lintas tenant.

### Sprint 4.3 - Service Router Mapping

Tasks:

- Implement service router mapping endpoint.
- Update customer router mapping.
- Enforce activation guard.

Acceptance:

- Service Internet aktif hanya setelah mapping valid.

### Sprint 4.4 - Radius AAA

Tasks:

- Implement Radius profile.
- Implement Radius user.
- Implement NAS device.
- Implement FreeRadius sync adapter interface.

Acceptance:

- Radius user terkait customer, service, router, profile.

### Sprint 4.5 - Suspend / Unsuspend

Tasks:

- Implement suspend service.
- Suspend Radius user.
- Implement unsuspend service.
- Reactivate Radius user.

Acceptance:

- Suspend/unsuspend sinkron antara service dan Radius user.

### Sprint 4.6 - Router Script Generator

Tasks:

- Implement template rendering.
- Support ROS6 PPPoE, ROS7 PPPoE, ROS6 Hotspot, ROS7 Hotspot.
- Audit every generated script.

Acceptance:

- Script generated sesuai input dan tercatat audit log.

## Phase 5 - Ticket & Work Order

### Sprint 5.1 - Ticket

Tasks:

- Implement ticket CRUD.
- Link ticket to customer, service, router, incident.
- Implement SLA fields.

Acceptance:

- Ticket teknis dapat mengacu ke router.

### Sprint 5.2 - Work Order

Tasks:

- Implement work order CRUD.
- Assign technician.
- Store report, photo metadata, GPS metadata.

Acceptance:

- Work order dapat dibuat dari ticket atau incident.

## Phase 6 - Monitoring & NOC

### Sprint 6.1 - SNMP Status Ingestion

Tasks:

- Implement monitoring ingestion endpoint/job.
- Update router SNMP status.
- Emit `router.up`, `router.down`, `router.snmp.failed`.

Acceptance:

- SNMP status Reachable, Unreachable, Auth Failed, Not Configured tampil.

### Sprint 6.2 - Capacity Dashboard

Tasks:

- Store capacity history.
- Detect warning/critical threshold.
- Build query for dashboard.

Acceptance:

- Warning/critical event terbit sesuai threshold.

### Sprint 6.3 - Customer Impact Analysis

Tasks:

- Calculate affected customers.
- Calculate affected services.
- Calculate affected Radius users.
- Calculate revenue impact.
- Generate incident.

Acceptance:

- Router Down menghasilkan impact report dan incident.

## Phase 7 - OSS Advanced

Tasks:

- OLT/ONU planning.
- IPAM.
- VLAN registry.
- CID generator.
- Revenue assurance checks.

Exit Criteria:

- IP/VLAN/CID unik per tenant.

## Phase 8 - GIS & Inventory

Tasks:

- GIS router/customer/fiber layer.
- Inventory asset.
- Link router asset to router object.

Exit Criteria:

- Router dan customer tampil di map.

## Phase 9 - Partner & SaaS

Tasks:

- Partner portal.
- Commission engine.
- License management.
- Usage metering.
- White label settings.

Exit Criteria:

- Usage metering mencakup customer count, router count, API usage, storage.

## Phase 10 - Marketplace & AI

Tasks:

- Connector marketplace foundation.
- FreeRadius connector packaging.
- Router SNMP connector packaging.
- AI NOC/anomaly prototype.

Exit Criteria:

- Connector dapat diaktifkan per tenant.

