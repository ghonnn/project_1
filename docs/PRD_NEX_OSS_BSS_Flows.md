# PRD NEX OSS/BSS - Flow Bisnis Utama

Peran: Senior Business Analyst OSS/BSS ISP

Dokumen ini merevisi flow bisnis agar sesuai kondisi implementasi saat ini. Prinsip topology tetap router-centric, tetapi UI disederhanakan: role router tidak menjadi field utama operator, status SNMP bukan pilihan manual, dan angka online berasal dari accounting aktif FreeRadius.

## Prinsip Topology

Flow jaringan utama:

`Customer -> Service -> Router -> Radius NAS -> FreeRadius SQL -> MikroTik PPPoE/Hotspot`

Untuk monitoring:

`Router -> SNMP Test/Polling -> Router Status -> Dashboard/NOC`

Untuk sesi online:

`MikroTik Accounting -> FreeRadius -> radacct -> Dashboard/List Router`

## Kondisi Implementasi Saat Ini

Sudah berjalan:

- Customer, service, product/profile, invoice, payment dasar.
- Router, interface, NAS device, Radius server/profile/user.
- Service provisioning ke Radius user.
- FreeRadius SQL sync untuk NAS dan user.
- Script MikroTik untuk Radius, SNMP, PPPoE, Hotspot, pool, dan isolir.
- Status SNMP dari test aplikasi ke router.
- Online count dari `radacct` aktif.
- Partner data dan field komisi dasar.

Belum lengkap:

- Work order lengkap.
- Payment gateway dan notification engine produksi.
- SNMP polling scheduler.
- NOC incident dan customer impact otomatis.
- GIS topology dan inventory detail.

## Aturan Umum Flow

- Billing hanya berjalan untuk service valid.
- Service jaringan wajib memiliki router mapping dan Radius user.
- Radius user harus terkait customer, service, router, dan profile/group.
- NAS device harus terkait router dan Radius server.
- Secret Radius dibuat otomatis oleh aplikasi dan dipakai di script MikroTik.
- Status SNMP tidak boleh diedit manual; status berasal dari test/polling.
- Langganan Online dan Voucher Online tidak boleh diinput manual; angka berasal dari `radacct` dengan `acctstoptime IS NULL`.

## 1. Customer Lifecycle

Diagram:

`Customer Created -> Service Created -> Router Selected -> Radius Profile Selected -> Service Provisioning -> Radius User Synced -> Billing Active -> Active Customer -> Suspend/Unsuspend/Terminate`

Output:

- Customer master.
- Service instance.
- Router mapping.
- Radius user.
- Invoice/payment.
- Audit log.

Status implementasi:

- Customer dan service tersedia.
- Provisioning service ke router/radius tersedia.
- Contract formal dan work order instalasi belum lengkap.

## 2. Service Provisioning Flow

Diagram:

`Create Service -> Select Customer/Product/Connection Type -> Select Router -> Select Radius Server -> Validate Router/NAS -> Create Radius User -> Sync FreeRadius SQL -> Service Active`

Rules:

- PPPoE/Hotspot/WiFi membutuhkan Router dan Radius server.
- NAS client dibuat dari Router melalui action provisioning.
- Radius group mengikuti product/profile.
- Username dan password Radius disimpan di Radius user.

Output:

- Service active.
- Radius user active.
- Data `radcheck`, `radreply`, `radusergroup`.
- NAS client di tabel `nas`.

## 3. Router Provisioning Flow

Diagram:

`Create Router -> Auto Hostname from Router Name -> Auto Generate Radius Secret -> Fill IP/SNMP/Pool Settings -> Connect Radius -> Download MikroTik Script -> Apply Script on MikroTik -> Test SNMP`

Rules:

- Nama router menjadi hostname.
- Secret Radius auto-generate 12 digit.
- Router role tidak menjadi input operator pada MVP.
- Status SNMP tampil dari hasil test.
- PPPoE/Hotspot interface diisi pada setting script MikroTik bila ingin script membuat server otomatis.

Output:

- Router.
- NAS device.
- MikroTik script.
- SNMP status.

## 4. FreeRadius Sync Flow

Diagram:

`Router Connected to Radius -> NAS Synced -> Service Provisioned -> Radius User Synced -> FreeRadius Reads SQL -> MikroTik Auth Request -> Accept/Reject`

Tables:

- `nas`
- `radcheck`
- `radreply`
- `radusergroup`
- `radgroupcheck`
- `radgroupreply`
- `radacct`

Rules:

- FreeRadius server harus membaca database aplikasi.
- MikroTik harus memakai secret yang sama dengan tabel `nas`.
- IP sumber MikroTik harus cocok dengan `nas.nasname`.
- Untuk online count, FreeRadius accounting harus aktif dan menulis `radacct`.

## 5. PPPoE/Hotspot Online Flow

Diagram:

`Client Connected -> MikroTik PPPoE/Hotspot Auth -> FreeRadius Auth OK -> Accounting Start -> radacct row with acctstoptime NULL -> Dashboard/List Router Online Count`

Rules:

- `Langganan Online` = PPP/PPPoE service yang punya session aktif di `radacct`.
- `Voucher Online` = Hotspot/WiFi service yang punya session aktif di `radacct`.
- User active yang belum connect tidak dihitung online.
- Accounting stop harus mengisi `acctstoptime`, sehingga sesi tidak dihitung online lagi.

## 6. Billing Flow

Diagram:

`Service Active -> Generate Invoice -> Payment Recorded -> Invoice Paid -> Unsuspend Evaluation`

Rules:

- Invoice dibuat untuk service valid.
- Payment manual tersedia.
- Paid invoice dapat memicu evaluasi unsuspend.

Belum lengkap:

- Gateway callback.
- Rekonsiliasi otomatis.
- Receipt dan notification lengkap.

## 7. Suspend / Unsuspend Flow

Diagram:

`Invoice Overdue -> Suspend Service -> Move/Disable Radius User -> Customer Isolated -> Payment Paid -> Activate Radius User -> Service Active`

Rules:

- Suspend dilakukan di service/radius user.
- User dapat dipindahkan ke profile isolir.
- Unsuspend mengaktifkan kembali Radius user.

## 8. SNMP Monitoring Flow

Diagram:

`Router Created -> SNMP Config in Script -> Script Applied on MikroTik -> Test SNMP from App -> Update SNMP Status -> Show in Router List/Dashboard`

Rules:

- SNMP status bukan pilihan manual.
- Status saat ini: Not Configured, Reachable, Unreachable.
- Polling periodik belum tersedia; saat ini test manual.

## 9. Ticket Flow

Diagram target:

`Ticket Created -> Link Customer/Service/Router -> Assign -> Investigate -> Resolve -> Close`

Status implementasi:

- Ticket resource dasar tersedia.

Belum lengkap:

- SLA.
- Work order.
- Technician assignment.
- Evidence report.

## 10. Partner Flow

Diagram:

`Partner Registered -> Customer/Service Linked to Partner -> Payment Reconciled -> Commission Calculated -> Payout`

Status implementasi:

- Menu Partner dan data Partner tersedia.
- Field partner/commission tersedia di customer/service.

Belum lengkap:

- Commission calculation otomatis.
- Partner portal.
- Payout approval.

## 11. NOC Impact Flow

Diagram target:

`Router Down -> Find Services -> Find Customers -> Find Radius Users -> Calculate Revenue Impact -> Create Incident/Ticket`

Status implementasi:

- Router status dan SNMP test tersedia.
- Online session count tersedia dari accounting.

Belum lengkap:

- Incident automation.
- Customer impact report.
- Revenue impact.
- NOC dashboard khusus.

## Dependency Antar Flow

1. Tenant/RBAC -> Customer/Product/Service.
2. Router/Radius -> Service Provisioning.
3. FreeRadius SQL sync -> MikroTik Auth.
4. FreeRadius Accounting -> Online Dashboard.
5. Billing -> Suspend/Unsuspend.
6. Ticket/Work Order -> NOC operation.

## QC Checklist

- Status SNMP tidak diinput manual.
- Online count tidak berasal dari user terdaftar.
- `radacct` wajib menjadi sumber sesi online.
- Partner menggantikan label Mitra di UI.
- Router tetap menjadi pusat provisioning jaringan.
