# NEX OSS/BSS ISP Cloud Platform - Focused MVP Sprint PRD

Status: focused sprint update for backend MVP end-to-end testing  
Date: 2026-06-20  
Role context: Senior Laravel Backend Engineer + Frontend Expert  
Scope: Phase 1, Phase 2, Phase 3 only.

## 1. Sprint Objective

Laravel sudah bisa diakses via browser/API. Sprint ini tidak menambah semua modul PRD v3.4 sekaligus. Fokus sprint adalah membuat backend MVP siap diuji end-to-end untuk alur inti ISP:

```text
Tenant + Customer + Service + Router
-> Radius + NAS + Router Mapping
-> Billing + Payment + Suspend/Unsuspend
```

Target akhirnya: satu tenant bisa membuat customer, membuat service internet, mapping service ke router/interface, membuat Radius user/NAS, mengaktifkan service, membuat invoice, mencatat payment, suspend, dan unsuspend dengan alur yang konsisten.

## 2. Product Rule yang Dikunci

```text
Customer -> Service -> Router -> Router Interface -> Radius NAS -> FreeRadius
```

Aturan wajib:

- POP dan BTS tidak menjadi modul atau tabel terpisah.
- POP dan BTS hanya nilai `router_role`: `pop_router` dan `bts_router`.
- Service network wajib punya router mapping jika `requires_router_mapping = true`.
- Service yang membutuhkan AAA wajib punya Radius user jika `requires_radius = true`.
- Radius NAS wajib selalu terhubung ke Router.
- Invoice hanya boleh dibuat untuk service aktif.
- Suspend/unsuspend dilakukan pada service, lalu status Radius user mengikuti.

## 3. Gap yang Dirapihkan untuk Sprint Ini

Gap awal yang ditemukan:

1. NAS Device Management UI/API belum lengkap.
2. Router Impact Analysis endpoint belum ada.
3. Router Script Generator masih hardcoded, belum memakai `router_script_templates`.
4. `router_capacity_history` belum ada.
5. Contract lifecycle belum masuk Laravel MVP.
6. Ticket SLA dan Work Order lifecycle masih tipis.
7. Tenant isolation perlu test coverage.
8. Billing automation masih manual.

Untuk sprint ini, gap tersebut dirapihkan menjadi tiga kelompok:

### 3.1 Masuk Sprint Phase 1-3

| Gap | Fase | Keputusan Sprint |
|---|---:|---|
| NAS Device Management UI/API belum lengkap | Phase 2 | Wajib dikerjakan sekarang. NAS adalah jembatan Router ke FreeRadius. |
| Service Router Mapping UI belum lengkap | Phase 2 | Wajib dikerjakan sekarang. Tanpa ini service internet tidak bisa siap aktivasi secara rapi. |
| Router Script Generator masih hardcoded | Phase 2 | Dikerjakan sebagai template MVP, cukup untuk ROS6/ROS7 PPPoE dan Hotspot. |
| Tenant isolation perlu test coverage | Phase 1 | Wajib dikerjakan sekarang untuk menjaga SaaS multi-tenant sejak awal. |
| Billing automation masih manual | Phase 3 | Rapihkan MVP manual dulu, lalu tambah payment-triggered unsuspend evaluation. |

### 3.2 Ditunda setelah Phase 3

| Gap | Alasan Ditunda |
|---|---|
| Router Impact Analysis endpoint belum ada | Masuk Phase 4/NOC. Tidak perlu menghambat billing/provisioning MVP. |
| `router_capacity_history` belum ada | Masuk Phase 5/Monitoring & Capacity. Belum wajib untuk end-to-end billing/provisioning. |
| Contract lifecycle belum masuk Laravel MVP | Masuk setelah billing MVP stabil. Untuk sprint ini cukup simpan contract reference di service metadata bila perlu. |
| Ticket SLA dan Work Order lifecycle masih tipis | Masuk Phase 4/Field Operation. Tidak perlu menghambat Phase 1-3. |

## 4. Sprint Flow yang Harus Bisa Diuji

```text
1. Platform Owner login admin/API
2. Buat Tenant
3. Buat Customer dalam Tenant
4. Buat Service Category dengan flags:
   - requires_router_mapping = true
   - requires_radius = true
   - requires_ip_assignment = true/false
   - requires_vlan = true/false
5. Buat Product
6. Buat Service untuk Customer
7. Buat Router
8. Buat Router Interface
9. Buat NAS Device yang terhubung ke Router
10. Mapping Service ke Router/Interface
11. Buat Radius Profile
12. Buat Radius User untuk Service + Router
13. Activate Service
14. Generate Invoice untuk Service aktif
15. Record Payment
16. Suspend Service
17. Unsuspend Service setelah payment/evaluation
```

## 5. Phase 1 - Tenant + Customer + Service + Router

### 5.1 Tujuan

Merapihkan fondasi multi-tenant dan master data inti agar semua data operasional bisa diuji dengan aman.

### 5.2 Scope Backend

- Tenant CRUD sudah ada, rapihkan validasi dan visibility.
- Customer CRUD tenant-safe.
- Service Category CRUD dengan flags wajib.
- Product CRUD linked ke Service Category.
- Service CRUD linked ke Customer, Product, Service Category.
- Router CRUD dengan controlled `router_role`.
- Router Interface CRUD linked ke Router.
- Tenant isolation middleware/test untuk entity utama.

### 5.3 Scope Frontend / Filament

- Tenant Resource.
- Customer Resource.
- Product Resource.
- Service Resource.
- Router Resource.
- Router Interface Resource.
- Field wajib dibuat jelas: tenant, customer, service category, router role, management IP, status.

### 5.4 Acceptance Criteria Phase 1

- Tenant bisa dibuat dari admin/API.
- Customer hanya muncul di tenant yang benar.
- Service hanya bisa dibuat untuk customer di tenant yang sama.
- Product hanya bisa menggunakan service category di tenant yang sama.
- Router role hanya menerima value PRD: `core_router`, `aggregation_router`, `edge_router`, `pppoe_router`, `bng`, `wireless_gateway`, `pop_router`, `bts_router`.
- Router Interface hanya bisa dibuat untuk router di tenant yang sama.
- User Tenant A tidak bisa read/write data Tenant B.

## 6. Phase 2 - Radius + NAS + Router Mapping

### 6.1 Tujuan

Membuat provisioning MVP benar-benar usable: service internet bisa ditelusuri ke router, interface, NAS, Radius profile, dan Radius user.

### 6.2 Scope Backend

- NAS Device API lengkap:
  - list
  - create
  - show
  - update
- NAS Device wajib punya `router_id`.
- NAS Device router harus tenant yang sama.
- Service Router Mapping API dirapihkan:
  - service_id
  - router_id
  - interface_id optional tapi harus satu router jika diisi
  - vlan_id optional
  - is_primary
- Customer Router Mapping otomatis dibuat saat service-router mapping dibuat.
- Radius Server CRUD.
- Radius Profile CRUD.
- Radius User CRUD.
- Radius User network service wajib punya router.
- Radius User service/customer/router harus tenant yang sama.
- Router Script Generator memakai template MVP, bukan inline hardcoded logic.

### 6.3 Scope Frontend / Filament

- `NasDeviceResource`.
- Service Router Mapping relation manager di Service Resource, atau resource terpisah jika lebih cepat.
- Radius Server Resource.
- Radius Profile Resource.
- Radius User Resource.
- Router Script Generator page tetap ada, tetapi sumber script dari template.
- Router Script Template Resource minimal untuk admin/developer.

### 6.4 Template Script MVP

Minimal template yang harus tersedia:

- MikroTik ROS6 PPPoE RADIUS.
- MikroTik ROS7 PPPoE RADIUS.
- MikroTik ROS6 Hotspot RADIUS.
- MikroTik ROS7 Hotspot RADIUS.

Variabel template:

```text
{{radius_service}}
{{radius_server_ip}}
{{radius_secret}}
{{auth_port}}
{{acct_port}}
{{router_hostname}}
{{interim_update}}
```

### 6.5 Acceptance Criteria Phase 2

- NAS Device bisa dibuat via API dan Filament.
- NAS Device tidak bisa dibuat tanpa Router.
- NAS Device tidak bisa memakai Router dari tenant lain.
- Service yang `requires_router_mapping = true` tidak bisa active sebelum mapping dibuat.
- Service Router Mapping otomatis membuat Customer Router Mapping.
- Radius User untuk service yang `requires_radius = true` wajib memiliki Router.
- Radius User tidak bisa memakai customer/service/router lintas tenant.
- Router Script Generator menghasilkan script dari `router_script_templates`.
- Audit log tercatat untuk create/update router, mapping, Radius user, dan script generation.

## 7. Phase 3 - Billing + Payment + Suspend

### 7.1 Tujuan

Merapihkan billing MVP agar alur invoice-payment-suspend-unsuspend bisa diuji end-to-end tanpa menunggu full recurring billing engine.

### 7.2 Scope Backend

- Invoice create hanya untuk service `active`.
- Invoice item wajib mengacu ke service dalam tenant yang sama.
- Payment create mengubah paid amount invoice.
- Invoice status:
  - `issued`
  - `partial_paid`
  - `paid`
  - `overdue`
  - `cancelled`
- Service suspend action:
  - set service `suspended`
  - set `suspended_at`
  - suspend related Radius users
  - write audit log
- Service unsuspend action:
  - set service `active`
  - clear `suspended_at`
  - activate related Radius users
  - write audit log
- Billing Unsuspend Evaluation:
  - jika invoice terkait service sudah paid, service boleh di-unsuspend
  - untuk MVP bisa endpoint manual/action admin dulu, belum perlu scheduler kompleks

### 7.3 Scope Frontend / Filament

- Invoice Resource: create/list/detail.
- Payment Resource: create/list.
- Service Resource action: activate, suspend, unsuspend.
- Status badges jelas untuk service, invoice, payment, Radius user.

### 7.4 Acceptance Criteria Phase 3

- Invoice gagal dibuat untuk service non-active.
- Invoice gagal dibuat jika service bukan milik tenant yang sama.
- Payment menambah `paid_amount` invoice.
- Payment partial membuat invoice `partial_paid`.
- Payment penuh membuat invoice `paid`.
- Suspend service mengubah status service dan Radius user menjadi suspended.
- Unsuspend service mengubah status service dan Radius user menjadi active.
- Paid invoice bisa menjalankan unsuspend evaluation untuk service terkait.
- Semua aksi billing dan status lifecycle masuk audit log.

## 8. Backlog Sprint yang Sudah Difokuskan

Urutan kerja final sprint:

```text
Phase 1
Tenant Isolation Tests
-> Customer/Service/Router validation cleanup
-> Router Interface tenant validation

Phase 2
NAS Management API/UI
-> Service Router Mapping UI
-> Radius User tenant validation
-> Router Script Templates

Phase 3
Invoice/Payment validation cleanup
-> Service Suspend/Unsuspend hardening
-> Billing Unsuspend Evaluation
```

## 9. Endpoint yang Perlu Diprioritaskan

### Phase 1

```text
GET    /api/v1/tenants/{tenant_id}/customers
POST   /api/v1/tenants/{tenant_id}/customers
GET    /api/v1/tenants/{tenant_id}/services
POST   /api/v1/tenants/{tenant_id}/services
PATCH  /api/v1/tenants/{tenant_id}/services/{id}
GET    /api/v1/tenants/{tenant_id}/routers
POST   /api/v1/tenants/{tenant_id}/routers
POST   /api/v1/tenants/{tenant_id}/router-interfaces
```

### Phase 2

```text
GET    /api/v1/tenants/{tenant_id}/nas-devices
POST   /api/v1/tenants/{tenant_id}/nas-devices
GET    /api/v1/tenants/{tenant_id}/nas-devices/{id}
PUT    /api/v1/tenants/{tenant_id}/nas-devices/{id}
POST   /api/v1/tenants/{tenant_id}/services/{service_id}/router-mapping
GET    /api/v1/tenants/{tenant_id}/radius/servers
POST   /api/v1/tenants/{tenant_id}/radius/servers
GET    /api/v1/tenants/{tenant_id}/radius/profiles
POST   /api/v1/tenants/{tenant_id}/radius/profiles
GET    /api/v1/tenants/{tenant_id}/radius/users
POST   /api/v1/tenants/{tenant_id}/radius/users
POST   /api/v1/tenants/{tenant_id}/router-script-generator
```

### Phase 3

```text
GET    /api/v1/tenants/{tenant_id}/invoices
POST   /api/v1/tenants/{tenant_id}/invoices
POST   /api/v1/tenants/{tenant_id}/payments
POST   /api/v1/tenants/{tenant_id}/invoices/{id}/evaluate-unsuspend
PATCH  /api/v1/tenants/{tenant_id}/services/{id} action=suspend
PATCH  /api/v1/tenants/{tenant_id}/services/{id} action=unsuspend
```

## 10. Yang Tidak Dikerjakan di Sprint Ini

Agar sprint tetap selesai, item berikut ditunda:

- Router Impact Analysis endpoint.
- Router Capacity Dashboard.
- `router_capacity_history` production pipeline.
- Full Contract lifecycle.
- Full Ticket SLA lifecycle.
- Full Work Order lifecycle.
- Payment gateway callback.
- WhatsApp notification.
- Customer portal/mobile app.
- GIS, inventory, OLT/ONU advanced.

## 11. Definition of Done Sprint

Sprint dianggap selesai jika satu skenario ini bisa dijalankan tanpa edit database manual:

```text
Create Tenant
-> Create Customer
-> Create Service Category requires router + radius
-> Create Product
-> Create Service
-> Create Router
-> Create Router Interface
-> Create NAS Device
-> Create Service Router Mapping
-> Create Radius Profile
-> Create Radius User
-> Activate Service
-> Create Invoice
-> Record Payment
-> Suspend Service
-> Unsuspend Service
```

Output teknis wajib:

- API response konsisten.
- Validasi tenant aman.
- Filament admin bisa menjalankan flow utama.
- Audit log muncul untuk aksi kritikal.
- Tests minimal untuk tenant isolation, activation guard, invoice/payment, suspend/unsuspend.

## 12. Rekomendasi Implementasi

Jangan mulai dari Router Impact, Capacity, Contract, Ticket SLA, atau Work Order. Itu penting, tapi bukan blocker untuk end-to-end MVP Phase 1-3.

Implementasi terbaik sekarang:

```text
1. Tenant-safe validation
2. NAS Device API + Filament
3. Service Router Mapping UI
4. Router Script Template MVP
5. Invoice/payment hardening
6. Billing unsuspend evaluation
7. Feature tests
```

Dengan urutan ini, MVP bisa diuji oleh tim NEX secara nyata sebelum masuk ke NOC, monitoring, ticket SLA, work order, contract, dan SaaS advanced.
