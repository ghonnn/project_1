# PRD NEX OSS/BSS - Flow Bisnis Utama v3.4 Router-Centric

Peran: Senior Business Analyst OSS/BSS ISP

Dokumen ini merevisi flow Step 1 agar konsisten dengan PRD v3.4. Prinsip topologi wajib:

`Customer -> Service -> Router -> Router Interface -> Radius NAS -> FreeRadius`

Router adalah node utama jaringan. POP dan BTS tidak menjadi modul/tabel utama; keduanya hanya nilai `router_role` seperti `POP Router` dan `BTS Router`.

## Gap Analysis v3.4

| Area | Gap Lama | Revisi v3.4 |
|---|---|---|
| Topologi | Flow belum menempatkan router sebagai node utama | Semua provisioning dan NOC menelusuri dampak dari Service ke Router/Interface/NAS |
| POP/BTS | Berisiko dianggap modul utama | POP/BTS hanya role pada Router |
| FreeRadius | Aktivasi AAA belum wajib terkait router | Radius user wajib terkait service dan router untuk layanan jaringan |
| NOC | Incident belum menghitung dampak pelanggan dan revenue dari router down | Ditambahkan Customer Impact Analysis Flow |
| Provisioning | Belum ada flow khusus router | Ditambahkan Router Provisioning Flow |

## Aturan Umum Flow

- Billing hanya berjalan untuk customer dengan contract valid dan service aktif.
- Service Internet wajib memiliki Router Mapping.
- Service non-network tidak wajib Router Mapping.
- Radius user dibuat dari service yang membutuhkan AAA dan harus dapat ditelusuri ke router.
- Router Down wajib menghasilkan daftar customer terdampak, radius user terdampak, revenue impact, dan incident/ticket.

## 1. Customer Lifecycle

Diagram:

`Prospect -> Lead -> Survey Work Order -> Feasible -> Quotation -> Contract Signed -> Installation Work Order -> Service Provisioning -> Router Mapping -> Radius User Active -> Billing Active -> Active Customer -> Suspend/Terminate`

Status:

- `prospect`
- `lead`
- `survey_requested`
- `feasible` / `not_feasible`
- `quoted`
- `contract_signed`
- `installation_scheduled`
- `provisioning`
- `active`
- `suspended`
- `terminated`

Actor:

- Sales
- Customer
- Teknisi
- NOC
- Billing
- Partner jika customer berasal dari partner

Output:

- Customer master
- Contract
- Service instance
- Installation work order
- Router/service mapping
- Radius user jika layanan membutuhkan AAA
- Billing account

## 2. Billing Flow

Diagram:

`Service Active -> Validate Contract -> Validate Service Category -> Validate Router Mapping if Internet -> Generate Invoice -> Deliver Invoice -> Dunning -> Paid or Suspend`

Trigger:

- Billing cycle
- Service activation
- One-time installation charge
- Contract renewal or amendment

Validation:

- Service harus `active`.
- Service Internet wajib memiliki `service_router_mapping`.
- Invoice item harus mengacu ke service yang valid.
- Invoice overdue memicu Suspend Flow sesuai policy tenant.

Output:

- Invoice
- Invoice item
- AR aging
- `invoice.created`
- `invoice.overdue`
- `invoice.paid`

## 3. Payment Flow

Diagram:

`Invoice Issued -> Payment Initiated -> Payment Captured -> Reconciliation -> Invoice Paid -> Unsuspend Evaluation -> Service/Radius Reactivation`

Trigger:

- Payment gateway callback
- Manual payment
- Partner collection
- Customer portal payment

Output:

- Payment record
- Receipt
- Invoice status update
- Unsuspend event jika invoice terkait service suspended

## 4. Suspend / Unsuspend Flow

Diagram:

`Invoice Overdue -> Dunning Policy -> Suspend Service -> Disable Radius User -> Notify Customer -> Payment Reconciled -> Enable Radius User -> Unsuspend Service`

Rules:

- Suspend dilakukan pada service, bukan langsung pada customer global kecuali policy tenant mengatur full-account suspension.
- Service suspend wajib mencatat alasan, invoice, dan actor/system trigger.
- Radius user terkait service diubah menjadi `suspended`.
- Unsuspend hanya dilakukan setelah payment cleared atau approval manual.

Output:

- Service status `suspended` atau `active`
- Radius user status `suspended` atau `active`
- Audit log
- Notification

## 5. NOC Flow

Diagram:

`Monitoring Alert -> Identify Router/Interface -> Customer Impact Analysis -> Incident Created -> Triage -> Escalation -> Work Order if Field Needed -> Resolution -> RCA -> Closure`

Trigger:

- SNMP alert
- PRTG/LibreNMS alert
- Radius authentication anomaly
- Customer ticket
- Manual NOC report

Output:

- Incident
- Ticket
- Impact list
- Work order
- RCA

## 6. Ticket Flow

Diagram:

`Ticket Created -> Categorize -> Link Customer/Service/Router -> Assign -> Investigate -> Resolve -> Customer Confirmation -> Close`

Rules:

- Ticket teknis harus dapat mengacu ke customer, service, router, atau incident.
- Ticket dari Router Down dapat dibuat otomatis dari incident.
- Ticket billing tetap mengacu ke invoice/payment, tetapi dapat mengait ke service bila berdampak suspend.

## 7. Work Order Flow

Diagram:

`Work Order Created -> Scope Router/Service/Customer -> Assign Technician -> Schedule -> Execute -> Upload Evidence -> Verify -> Close`

Rules:

- Work order instalasi harus mengacu ke customer dan service.
- Work order network harus dapat mengacu ke router, router interface, atau router link.
- Completion report wajib memuat foto, GPS, checklist, dan hasil provisioning/repair.

## 8. Partner Commission Flow

Diagram:

`Partner Sale -> Contract Signed -> Service Active -> Invoice Issued -> Payment Reconciled -> Commission Calculated -> Approval -> Payout`

Rules:

- Komisi tidak dihitung dari invoice unpaid, cancelled, atau written-off.
- Komisi recurring mengacu ke payment yang sudah reconciled.
- Partner tidak mendapat akses ke router detail kecuali data ringkas layanan milik customer partner.

## 9. Contract Flow

Diagram:

`Offer Accepted -> Draft Contract -> Review -> E-Signature -> Active Terms -> Service Order -> Renewal/Amendment/Termination`

Rules:

- Contract signed wajib ada sebelum billing recurring aktif.
- SLA dan service category flags disimpan sebagai term yang memengaruhi provisioning.
- Contract dapat menentukan apakah service wajib router mapping dan radius user.

## 10. FreeRadius Activation Flow

Diagram:

`Service Provisioning -> Validate Router Mapping -> Select Radius Profile -> Create Radius User -> Link Router/NAS -> Sync FreeRadius -> Test Authentication -> Activate Service`

Rules:

- FreeRadius adalah source of truth untuk authentication phase awal.
- Radius user wajib mengacu ke customer, service, router, dan radius profile jika service membutuhkan akses jaringan.
- Radius NAS wajib mengacu ke router.
- Secret disimpan terenkripsi dan hanya ditampilkan saat creation/reset.

Output:

- Radius user
- Radius profile assignment
- NAS mapping
- Authentication test result
- `radius.user.created`

## 11. Router Provisioning Flow

Diagram:

`Create Router -> Assign Router Role -> Create Interface -> Assign SNMP Monitoring -> Assign Radius NAS -> Map Service -> Map Customer`

Router role:

- Core Router
- Aggregation Router
- Edge Router
- PPPoE Router
- BNG
- Wireless Gateway
- POP Router
- BTS Router

Rules:

- Router wajib memiliki tenant, hostname/router name, role, status, dan management IP.
- Interface dibuat di bawah router.
- SNMP dapat berstatus `Reachable`, `Unreachable`, `Auth Failed`, atau `Not Configured`.
- Radius NAS dibuat jika router berperan sebagai NAS/BNG/PPPoE/Hotspot gateway.

## 12. Customer Impact Analysis Flow

Diagram:

`Router Down -> Find Service Mapping -> Find Customer -> Find Radius User -> Calculate Revenue Impact -> Generate Incident`

Rules:

- Impact dihitung dari `service_router_mapping`, `customer_router_mapping`, dan radius user terkait.
- Revenue impact menggunakan MRR/recurring amount dari service aktif terdampak.
- Incident harus memuat severity, router, interface jika diketahui, customer count, service count, radius user count, dan revenue impact.

Output:

- Router impact report
- Incident
- Optional ticket/work order
- Customer notification batch

## Dependency Antar Flow

- Customer Lifecycle -> Contract -> Work Order -> Router Provisioning -> FreeRadius Activation -> Billing.
- Billing -> Suspend -> Radius disable.
- Payment -> Unsuspend -> Radius enable.
- Router Provisioning -> NOC Monitoring.
- Router Down -> Customer Impact Analysis -> Incident -> Ticket/Work Order.
- Partner Commission bergantung pada Contract, Invoice, dan Payment Reconciled.

## QC Checklist

- Tidak ada flow yang menghubungkan customer langsung ke POP atau BTS.
- Semua layanan jaringan melewati Service -> Router -> Router Interface.
- POP Router dan BTS Router hanya role pada Router.
- Router Down menghasilkan customer impact, radius impact, revenue impact, dan incident.
