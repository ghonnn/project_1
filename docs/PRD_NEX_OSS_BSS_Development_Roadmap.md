# PRD NEX OSS/BSS - Development Roadmap

Peran: Technical Product Owner

Dokumen ini merevisi roadmap agar sesuai dengan kondisi implementasi aplikasi saat ini. Status ini mengacu pada kode Laravel/Filament, skema database, Docker deployment, dan integrasi FreeRadius yang sudah ada di branch `feat/mvp-phase-1-3-backend`.

## Status Implementasi Saat Ini

Kesimpulan singkat: aplikasi saat ini sudah melewati fondasi MVP dan berada di akhir Phase 4, dengan sebagian awal Phase 6, Phase 8, dan Phase 9 sudah mulai terbentuk. Phase 5 Ticket/Work Order belum lengkap, sehingga secara roadmap produk belum bisa disebut selesai Phase 6 penuh.

| Phase | Nama | Status Saat Ini | Catatan |
|---|---|---|---|
| Phase 0 | Discovery & Blueprint | Selesai dasar | PRD, roadmap, flow, role matrix, API contract, database design tersedia. |
| Phase 1 | Core Foundation | Selesai MVP | Auth Filament, RBAC, tenant, customer, product, service category, audit log tersedia. |
| Phase 2 | Billing MVP | Selesai MVP | Invoice, invoice item, payment manual, paid invoice, unpaid invoice, billing setting dasar tersedia. |
| Phase 3 | Payment & Notification | Sebagian | Payment record dan rekonsiliasi dasar tersedia; gateway/webhook dan WhatsApp/email otomatis belum final. |
| Phase 4 | Router Management, FreeRadius AAA, Suspend Engine | Hampir selesai MVP | Router, interface, NAS, Radius server/profile/user, service provisioning, FreeRadius SQL sync, MikroTik script, suspend/activate tersedia. |
| Phase 5 | Ticket & Work Order | Awal | Ticket resource tersedia; work order, SLA, technician assignment, evidence field report belum lengkap. |
| Phase 6 | Monitoring & NOC | Sebagian awal | SNMP test/status router tersedia, dashboard count online memakai `radacct`; belum ada NOC incident, capacity, impact automation. |
| Phase 7 | OSS Advanced | Awal placeholder | OLT/ODP/GenieACS page tersedia sebagai awal; IPAM/VLAN/capacity belum lengkap. |
| Phase 8 | GIS & Inventory | Awal placeholder | Map customer/ODP page tersedia; GIS topology dan inventory asset belum lengkap. |
| Phase 9 | Partner & SaaS | Sebagian awal | Partner data, commission fields, license counters, multi-tenant dasar tersedia; partner portal/white-label/usage metering belum lengkap. |
| Phase 10 | Marketplace & AI | Belum mulai | Connector marketplace dan AI NOC belum diimplementasikan. |

## Phase 0 - Discovery & Blueprint

Status: Selesai dasar.

Sudah ada:

- PRD roadmap dan flow bisnis.
- Database design.
- API contract MVP.
- Role matrix.
- Acceptance criteria MVP.
- Blueprint pre-coding.

Sisa:

- Menjaga dokumen tetap sinkron dengan kode saat fitur berubah.
- Menambahkan runbook operasional produksi.

## Phase 1 - Core Foundation

Status: Selesai MVP.

Sudah ada:

- Laravel 12 + Filament admin.
- Tenant management.
- User, role, permission matrix.
- Customer master.
- Product/profile layanan.
- Service category flags.
- Service instance.
- Audit log.
- UI theme clean light + emerald navigation.

Sisa:

- Hardening permission per tenant untuk semua edge case.
- Audit event lebih rinci untuk semua aksi massal.

## Phase 2 - Billing MVP

Status: Selesai MVP.

Sudah ada:

- Invoice dan invoice item.
- Payment record.
- Paid/unpaid invoice resources.
- Billing setting dasar.
- Auto suspend command untuk overdue service.
- Dashboard finance dasar.

Sisa:

- AR aging detail.
- Prorate dan recurring scheduler produksi.
- Receipt dan reporting finansial lengkap.

## Phase 3 - Payment & Notification

Status: Sebagian.

Sudah ada:

- Payment manual dan paid invoice flow.
- Unsuspend evaluation setelah pembayaran tersedia di service layer.
- Halaman pengaturan WhatsApp sebagai placeholder.

Belum lengkap:

- Payment gateway callback/webhook produksi.
- Rekonsiliasi otomatis mutasi bank/e-wallet.
- WhatsApp/email delivery engine.
- Template notifikasi invoice, overdue, paid, suspend.

## Phase 4 - Router Management, FreeRadius AAA, Suspend Engine

Status: Hampir selesai MVP.

Sudah ada:

- Router CRUD dengan nama router/hostname otomatis.
- Secret Radius auto-generate 12 digit.
- Router interface relation.
- Router script MikroTik untuk SNMP, Radius, PPP profile, PPPoE, Hotspot/WiFi, pool, isolir.
- Router action `Hubungkan Radius`.
- Router action `Test SNMP`.
- FreeRadius server CRUD dan test validasi konfigurasi.
- NAS device CRUD dan sync NAS ke SQL FreeRadius.
- Radius profile dan Radius user CRUD.
- Sync user ke tabel `radcheck`, `radreply`, `radusergroup`, `radgroupcheck`, `radgroupreply`.
- Sync NAS ke tabel `nas`.
- Tabel accounting `radacct` untuk sesi online aktif.
- Service provisioning membuat/menghubungkan Radius user, router, NAS, dan billing data.
- Suspend/activate Radius user.
- Product `mikrotik_group` dinormalisasi untuk group FreeRadius.

Penyesuaian dari PRD lama:

- `router_role` tidak lagi ditampilkan sebagai field UI utama. Untuk MVP, role disimpan default internal agar UX lebih sederhana.
- Status SNMP bukan input manual. Status berubah dari hasil `Test SNMP`.
- Online PPPoE/Hotspot bukan input manual. Angka online berasal dari accounting aktif `radacct.acctstoptime IS NULL`.

Sisa:

- Validasi end-to-end PPPoE dan Hotspot dari MikroTik produksi.
- Script variants ROS6/ROS7 yang lebih eksplisit.
- Reset/regenerate secret dengan audit.
- SNMP collector periodik, bukan hanya test manual.
- FreeRadius accounting harus dipastikan aktif di server Radius.

## Phase 5 - Ticket & Work Order

Status: Awal.

Sudah ada:

- Ticket resource dasar.

Belum lengkap:

- SLA policy.
- Work order.
- Technician assignment.
- Schedule.
- Photo/GPS/checklist field report.
- Link ticket ke incident/router/service secara lengkap.

Phase 5 harus diselesaikan sebelum NOC automation dianggap production-ready.

## Phase 6 - Monitoring & NOC

Status: Sebagian awal.

Sudah ada:

- SNMP status per router dari action `Test SNMP`.
- Router list menampilkan status SNMP.
- Router list menampilkan Langganan Online dan Voucher Online.
- Dashboard menampilkan SNMP aktif, Langganan Online, Voucher Online.
- Online count berbasis `radacct`, bukan user terdaftar.

Belum lengkap:

- SNMP polling scheduler.
- Interface traffic/capacity history.
- Router down incident generation.
- Customer impact analysis otomatis.
- NOC dashboard khusus.
- PRTG/LibreNMS connector.

## Phase 7 - OSS Advanced

Status: Awal placeholder.

Sudah ada:

- Halaman OLT.
- Halaman ODP.
- Halaman GenieACS.

Belum lengkap:

- Data model OLT/ONU detail.
- IPAM.
- VLAN management.
- Capacity planning.
- Revenue assurance.

## Phase 8 - GIS & Inventory

Status: Awal placeholder.

Sudah ada:

- Halaman Map Pelanggan.
- Halaman Map ODP.
- Field latitude/longitude pada beberapa entity.

Belum lengkap:

- GIS topology layer router/customer/fiber/ODC/ODP.
- Inventory asset lifecycle.
- Fiber path and splice management.

## Phase 9 - Partner & SaaS

Status: Sebagian awal.

Sudah ada:

- Menu Partner.
- Data Partner.
- Field partner pada customer/service.
- Field komisi partner.
- Tenant license counters.
- Multi-tenant foundation.

Belum lengkap:

- Partner portal.
- Commission engine dari paid invoice.
- Payout approval.
- White-label per tenant.
- Usage metering produksi.

## Phase 10 - Marketplace & AI

Status: Belum mulai.

Belum lengkap:

- Marketplace connector.
- AI NOC.
- Anomaly detection.
- Predictive churn.
- Connector packaging.

## Critical Path Berikutnya

1. Selesaikan validasi PPPoE/Hotspot end-to-end: MikroTik -> FreeRadius -> SQL -> aplikasi.
2. Pastikan FreeRadius accounting menulis ke `radacct`.
3. Selesaikan Ticket dan Work Order agar Phase 5 lengkap.
4. Tambahkan SNMP polling scheduler dan NOC dashboard agar Phase 6 naik dari partial ke MVP.
5. Lanjutkan GIS/inventory setelah router/radius/monitoring stabil.

## Definisi Phase Saat Ini

Produk saat ini dapat dinyatakan:

- Phase 1 selesai MVP.
- Phase 2 selesai MVP.
- Phase 3 partial.
- Phase 4 hampir selesai MVP.
- Phase 5 awal.
- Phase 6 partial awal.
- Phase 8 dan Phase 9 memiliki fondasi awal, belum production-ready.

Dengan kata lain, posisi proyek saat ini adalah **Phase 4 late MVP dengan beberapa komponen Phase 6/8/9 sudah dimulai**.
