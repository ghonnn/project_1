# PRD NEX OSS/BSS - Development Roadmap v3.4 Router-Centric

Peran: Technical Product Owner

Roadmap ini merevisi Step 5 agar mengikuti PRD v3.4. Urutan phase lama diganti penuh dengan Phase 0 sampai Phase 10 berikut.

## Gap Analysis v3.4

| Area | Gap Lama | Revisi v3.4 |
|---|---|---|
| Phase 4 | Fokus FreeRadius dan suspend saja | Menjadi Router Management, FreeRadius AAA, Suspend Engine |
| Monitoring | Belum eksplisit Router SNMP | Phase 6 fokus Monitoring & NOC dengan Router SNMP |
| GIS | Belum router layer sebagai dasar | Phase 8 GIS & Inventory memakai router/fiber/customer layer |
| Marketplace | Belum membatasi MikroTik API | Marketplace awal fokus FreeRadius Connector, Router SNMP, Script Generator |

## Phase Summary

| Phase | Nama | Prioritas | Output Utama |
|---|---|---|---|
| Phase 0 | Discovery & Blueprint | Wajib | Scope lock, process map, data model baseline, integration blueprint |
| Phase 1 | Core Foundation | Wajib | Auth, RBAC, tenant, customer, product, service category flags |
| Phase 2 | Billing MVP | Wajib | Invoice, recurring billing, prorate, AR aging, manual payment |
| Phase 3 | Payment & Notification | Wajib | Payment gateway, reconciliation, WhatsApp/email notification |
| Phase 4 | Router Management FreeRadius AAA Suspend Engine | Tinggi | Router, interface, NAS, Radius profile/user, RouterOS script, suspend/unsuspend |
| Phase 5 | Ticket & Work Order | Tinggi | Ticket, SLA, work order, field report, installation/maintenance flow |
| Phase 6 | Monitoring & NOC | Tinggi | Router SNMP, PRTG/LibreNMS, NOC dashboard, impact analysis |
| Phase 7 | OSS Advanced | Sedang | OLT/ONU, IPAM, VLAN, capacity planning, revenue assurance |
| Phase 8 | GIS & Inventory | Sedang | Router/customer/fiber map, ODC/ODP, asset tracking |
| Phase 9 | Partner & SaaS | Strategis | Partner portal, commission, white label, license, usage metering |
| Phase 10 | Marketplace & AI | Belakangan | Addon marketplace, AI NOC, anomaly detection, predictive churn |

## Phase 0 - Discovery & Blueprint

Objective:

- Mengunci scope MVP dan blueprint router-centric.

Deliverables:

- Business process map.
- Tenant and RBAC blueprint.
- Router-centric data model.
- Billing and payment policy.
- FreeRadius integration design.
- Monitoring integration decision.

Acceptance Criteria:

- POP/BTS disepakati sebagai Router Role, bukan modul utama.
- Service category flags `requires_router_mapping` dan `requires_radius` disetujui.
- Flow Radius: service active, radius user active, invoice overdue, suspend, paid, unsuspend disetujui.

## Phase 1 - Core Foundation

Objective:

- Membangun fondasi SaaS multi-tenant.

Scope:

- Auth and session.
- Tenant management.
- RBAC and permission matrix.
- Customer master.
- Product catalog.
- Service category.
- Service instance.
- Audit log.

Acceptance Criteria:

- Data tenant terisolasi.
- Customer dan service dapat dibuat.
- Service category memiliki flags router/radius.
- Audit log mencatat aksi penting.

## Phase 2 - Billing MVP

Objective:

- Menghasilkan billing MVP yang benar untuk ISP.

Scope:

- Invoice.
- Invoice items.
- Recurring billing.
- Prorate.
- AR aging.
- Manual payment record.
- Basic finance reports.

Acceptance Criteria:

- Invoice hanya dibuat untuk service valid.
- Invoice item mengacu ke service.
- Payment mengubah outstanding balance.
- AR aging dapat ditampilkan.

## Phase 3 - Payment & Notification

Objective:

- Menghubungkan pembayaran dan komunikasi pelanggan.

Scope:

- Payment gateway integration.
- Payment webhook.
- Reconciliation.
- Receipt.
- WhatsApp/email notification.
- Dunning notification.

Acceptance Criteria:

- Payment reconciled dapat menandai invoice paid.
- Paid invoice dapat memicu unsuspend evaluation.
- Notifikasi invoice, overdue, paid, dan suspend terkirim.

## Phase 4 - Router Management FreeRadius AAA Suspend Engine

Objective:

- Membangun inti OSS network activation berbasis router.

Scope:

- Router Management.
- Router Interface Management.
- Router Link Management.
- Radius NAS mapped to Router.
- Radius Profile.
- Radius User.
- Service Router Mapping.
- Customer Router Mapping.
- RouterOS Script Generator.
- Suspend/Unsuspend Engine.

Tasks Backend:

- CRUD `routers`, `router_interfaces`, `router_links`.
- CRUD `nas_devices`, `radius_profiles`, `radius_users`.
- Service activation validation for router mapping.
- Generate ROS6/ROS7 PPPoE and Hotspot scripts.
- Suspend service and disable Radius user.
- Unsuspend service and enable Radius user.

Tasks Frontend:

- Router Dashboard.
- Router Detail.
- Interface and link screens.
- Radius profile/user screens.
- Script Generator screen.
- Suspend/unsuspend controls.

Acceptance Criteria:

- Router dapat dibuat, diubah, dinonaktifkan.
- Router memiliki role Core, Aggregation, Edge, PPPoE, BNG, Wireless Gateway, POP Router, BTS Router.
- Service Internet wajib memiliki router mapping.
- Radius user aktif setelah service provisioning valid.
- Script generator menghasilkan ROS6 PPPoE, ROS7 PPPoE, ROS6 Hotspot, ROS7 Hotspot.
- Suspend mematikan Radius user; unsuspend mengaktifkannya kembali.

## Phase 5 - Ticket & Work Order

Objective:

- Menghubungkan support, NOC, dan field operation.

Scope:

- Ticket.
- SLA.
- Work Order.
- Technician assignment.
- Installation report.
- Maintenance report.
- Photo/GPS evidence.

Acceptance Criteria:

- Ticket dapat mengacu ke customer, service, router, incident.
- Work order dapat mengacu ke router, interface, link, atau service.
- Field report dapat diverifikasi dan ditutup.

## Phase 6 - Monitoring & NOC

Objective:

- Memberi visibility operasional jaringan dan dampak pelanggan.

Scope:

- Router SNMP Monitoring.
- Capacity Dashboard.
- PRTG/LibreNMS/SNMP integration.
- FreeRadius availability monitoring.
- NOC Dashboard.
- Customer Impact Analysis.
- Incident generation.

Acceptance Criteria:

- SNMP status menampilkan Reachable, Unreachable, Auth Failed, Not Configured.
- Router Down menampilkan customer terdampak, radius user terdampak, revenue impact.
- Capacity warning/critical memicu event.
- Incident dapat dibuat otomatis dari router down.

## Phase 7 - OSS Advanced

Objective:

- Memperluas OSS setelah core router/radius stabil.

Scope:

- OLT/ONU management.
- IPAM.
- VLAN management.
- CID generator.
- Capacity planning.
- Revenue assurance.

Acceptance Criteria:

- VLAN dan CID tidak duplikat per tenant.
- IPAM dapat mengacu ke service/router/interface.
- Capacity planning membaca histori router.

## Phase 8 - GIS & Inventory

Objective:

- Menyediakan peta dan inventory berbasis topology.

Scope:

- GIS Router Layer.
- GIS Customer Layer.
- Fiber Layer.
- ODC/ODP Layer.
- Inventory asset.
- Router as inventory asset and network object.

Acceptance Criteria:

- GIS menampilkan router, customer, fiber, ODC, ODP.
- Customer dapat ditelusuri ke router.
- Asset router dapat dikaitkan dengan object router.

## Phase 9 - Partner & SaaS

Objective:

- Menyiapkan model partner dan SaaS multi-tenant untuk dijual ke ISP lain.

Scope:

- Partner portal.
- Commission engine.
- White label.
- License management.
- Usage metering.
- Tenant billing.

Acceptance Criteria:

- Komisi dihitung dari payment reconciled.
- Usage metering mencakup customer count, router count, storage, API usage, WhatsApp usage.
- Tenant isolation berlaku untuk router, radius, customer, service, billing, monitoring.

## Phase 10 - Marketplace & AI

Objective:

- Menambahkan addon dan AI setelah platform stabil.

Scope:

- FreeRadius Connector.
- Router SNMP Monitoring Connector.
- Notification connector.
- AI NOC.
- Anomaly detection.
- Predictive churn.

Acceptance Criteria:

- Marketplace connector dapat diaktifkan per tenant.
- MikroTik API Integration tidak masuk phase awal.
- Script Generator tetap menjadi mekanisme RouterOS awal, bukan kontrol router via API.

## Critical Path

1. Core Foundation harus selesai sebelum billing dan router.
2. Billing harus selesai sebelum suspend engine.
3. Router Management dan FreeRadius menjadi prasyarat NOC monitoring.
4. Ticket/Work Order menjadi prasyarat eskalasi incident operasional.
5. SaaS dan Marketplace dilakukan setelah tenant isolation stabil.

## QC Checklist

- Roadmap memakai Phase 0 sampai Phase 10 sesuai PRD v3.4.
- Phase 4 berisi Router Management, FreeRadius AAA, dan Suspend Engine.
- Phase 6 berisi Router SNMP Monitoring, NOC, dan Impact Analysis.
- POP/BTS tidak menjadi phase atau modul terpisah.
