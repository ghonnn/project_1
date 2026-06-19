# PRD NEX OSS/BSS - MVP Acceptance Criteria v3.4 Router-Centric

Peran: QA Lead dan Technical Product Owner

Dokumen ini merevisi Step 9 agar acceptance criteria konsisten dengan PRD v3.4.

## Gap Analysis v3.4

| Area | Gap Lama | Revisi v3.4 |
|---|---|---|
| Router | Belum menjadi acceptance criteria utama | Ditambahkan CRUD router, role, SNMP, impact, capacity |
| Service | Router mapping belum wajib untuk Internet | Ditambahkan rule wajib mapping untuk service Internet |
| Radius | Radius belum dikaitkan ke router | Radius user wajib terkait service dan router jika layanan jaringan |
| Script | Belum ada output script generator | Ditambahkan ROS6/ROS7 PPPoE/Hotspot |

## Customer and Service

Feature: Customer and Service Lifecycle

Acceptance Criteria:

- Customer dapat dibuat, diubah, dibaca, dan dinonaktifkan sesuai role.
- Service dapat dibuat untuk customer dengan product dan contract valid.
- Service Internet wajib memiliki Router Mapping sebelum status `active`.
- Service non-network tidak wajib Router Mapping.
- Service dengan `requires_radius = true` wajib memiliki Radius User.
- Customer tidak pernah dihubungkan langsung ke POP/BTS; relasi selalu melalui Service -> Router.

Test Cases:

| Scenario | Expected Result |
|---|---|
| Activate Internet service tanpa router mapping | Ditolak dengan validation error |
| Activate non-network service tanpa router mapping | Berhasil jika rule lain valid |
| Read customer impact summary | Menampilkan service dan router terkait untuk role internal |

## Router Management

Feature: Router CRUD and Role

Acceptance Criteria:

- Router dapat dibuat, diubah, dan dinonaktifkan.
- Router memiliki role:
  - Core Router
  - Aggregation Router
  - Edge Router
  - PPPoE Router
  - BNG
  - Wireless Gateway
  - POP Router
  - BTS Router
- Hostname router unik per tenant.
- Router dapat memiliki interface.
- Router dapat memiliki router link.
- Router dapat dikaitkan ke Radius NAS.
- POP dan BTS tidak tersedia sebagai tabel/modul terpisah.

Test Cases:

| Scenario | Expected Result |
|---|---|
| Create router role POP Router | Router tersimpan sebagai `router_role`, bukan POP entity |
| Create duplicate hostname dalam tenant sama | Ditolak dengan conflict |
| Deactivate router yang masih punya primary active service mapping | Ditolak atau butuh override approval |

## Router Interface and Link

Acceptance Criteria:

- Router Interface dapat dibuat, diubah, dibaca.
- Router Interface memiliki state Provisioning, Up, Down, Disabled.
- Router Link dapat menghubungkan dua router dan optional interface.
- Interface yang digunakan active service tidak boleh disabled tanpa impact confirmation.

## SNMP Monitoring

Acceptance Criteria:

- SNMP Status dapat ditampilkan:
  - Reachable
  - Unreachable
  - Auth Failed
  - Not Configured
- Router SNMP status dapat diperbarui oleh monitoring service.
- SNMP failed menghasilkan event `router.snmp.failed`.
- Router Down menghasilkan event `router.down`.

Test Cases:

| Scenario | Expected Result |
|---|---|
| SNMP credential salah | Status `Auth Failed` |
| Router tidak bisa dihubungi | Status `Unreachable`, router menjadi Offline/Critical sesuai rule |
| SNMP belum dikonfigurasi | Status `Not Configured` |

## Capacity Dashboard

Acceptance Criteria:

- Dashboard menampilkan CPU, memory, traffic, utilization.
- Utilization warning menghasilkan `router.capacity.warning`.
- Utilization critical menghasilkan `router.capacity.critical`.
- Data histori tersimpan pada `router_capacity_history`.

## Customer Impact Analysis

Acceptance Criteria:

- Router Down wajib menampilkan customer terdampak.
- Router Down wajib menampilkan service terdampak.
- Router Down wajib menampilkan radius user terdampak.
- Router Down wajib menampilkan revenue impact.
- Router Down wajib dapat menghasilkan incident.

Test Cases:

| Scenario | Expected Result |
|---|---|
| Router down dengan 100 service aktif | Impact menampilkan 100 service dan customer terkait |
| Router down dengan radius users | Impact menampilkan radius user terdampak |
| Impact calculated | Revenue impact bulanan dihitung dari service aktif terdampak |

## Radius and FreeRadius AAA

Acceptance Criteria:

- Radius profile dapat dibuat dan diubah.
- Radius user dapat dibuat untuk service.
- Radius user memiliki state Pending, Active, Suspended, Terminated.
- Radius User wajib terhubung ke Customer, Service, Router, dan Radius Profile jika service membutuhkan akses jaringan.
- Radius NAS wajib memiliki Router Mapping.
- Suspend service mengubah Radius User menjadi Suspended.
- Unsuspend service mengubah Radius User menjadi Active.
- Secret disimpan terenkripsi dan tidak tampil ulang kecuali saat creation/reset.

## Router Script Generator

Acceptance Criteria:

- Script Generator wajib menghasilkan:
  - ROS6 PPPoE
  - ROS7 PPPoE
  - ROS6 Hotspot
  - ROS7 Hotspot
- Output script memuat konfigurasi Radius server, secret, dan service profile sesuai input.
- Generate script wajib masuk audit log.
- MikroTik API Integration tidak wajib pada MVP phase awal.

## Billing, Payment, Suspend

Acceptance Criteria:

- Invoice dapat dibuat untuk service aktif.
- Payment reconciled mengubah invoice menjadi paid.
- Invoice overdue dapat memicu suspend.
- Payment paid dapat memicu unsuspend.
- Suspend/unsuspend harus sinkron dengan Radius user.

## Ticket and Work Order

Acceptance Criteria:

- Ticket dapat dibuat dari customer, NOC alert, atau incident.
- Ticket teknis dapat mengacu ke router, interface, service, dan customer.
- Work Order dapat dibuat dari ticket atau incident.
- Work Order field report memuat bukti pekerjaan.

## Event-Driven Acceptance

Acceptance Criteria:

- Event router lifecycle tersedia: `router.created`, `router.updated`, `router.deleted`.
- Event monitoring tersedia: `router.up`, `router.down`, `router.capacity.warning`, `router.capacity.critical`, `router.snmp.failed`.
- Event mapping tersedia: `service.router.mapped`.
- Event impact tersedia: `customer.impact.detected`, `incident.created`.
- Semua consumer idempotent dan memiliki retry/DLQ.

## Security and Tenant Isolation

Acceptance Criteria:

- Semua entity tenant-scoped memiliki `tenant_id`.
- User tenant tidak dapat membaca router/radius/customer tenant lain.
- Platform Owner dapat mengakses lintas tenant untuk operasi platform.
- Semua aksi sensitif masuk audit log.

## Final MVP Gate

MVP v3.4 dianggap lolos jika:

- Service Internet bisa diaktifkan end-to-end sampai Router Mapping dan Radius User.
- Router Down dapat dihitung dampaknya sampai customer, radius user, revenue impact, dan incident.
- Billing, payment, suspend, dan unsuspend sinkron dengan Radius.
- Tidak ada konsep POP/BTS sebagai modul utama atau tabel terpisah.
