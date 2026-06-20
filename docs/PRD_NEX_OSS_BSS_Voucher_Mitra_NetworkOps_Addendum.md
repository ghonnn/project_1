# PRD NEX OSS/BSS - Addendum Voucher, Mitra, Network Ops & Platform Settings

Peran: Senior Business Analyst OSS/BSS ISP

Dokumen ini adalah addendum terhadap PRD v3.4 Router-Centric. Sumber referensi: aplikasi billing ISP eksisting (NEX Network / nexbilling.id) yang sudah dipakai operasional, dipetakan ulang ke 12 Domain Arsitektur di `docs/pre-coding/CURRENT_IMPLEMENTATION_FLOWCHART.md` agar konsisten dengan prinsip router-centric dan multi-tenant NEX OSS/BSS.

Prinsip yang tetap berlaku dari PRD v3.4:

- Router adalah node utama topologi (`Customer -> Service -> Router -> Router Interface -> Radius NAS -> FreeRadius`).
- POP/BTS hanya `router_role`, bukan modul/tabel terpisah.
- Tenant isolation wajib di semua modul baru pada dokumen ini.

## Status Legend

- `Built`: sudah ada model/migration/Filament Resource dengan CRUD nyata.
- `Skeleton`: sudah ada navigasi dan halaman Filament, tampilan/kolom mengikuti referensi, tapi data belum terhubung ke tabel nyata (placeholder).
- `Planned`: baru terdaftar di navigasi sebagai placeholder "coming soon", fungsi belum dibangun.

## 1. Menu Mitra (Partner & Reseller Domain)

| Item | Fungsi | Domain | Status |
|---|---|---|---|
| Data Mitra | CRUD reseller/mitra: kode, nama, outlet, no HP, alamat, jenis & nilai komisi, saldo, status aktif/nonaktif | Partner & Reseller Domain | `Built` (`MitraResource`, tabel `mitras`) |

Catatan:

- Mitra adalah pihak yang menjual voucher/layanan atas nama tenant dan mendapat komisi per transaksi.
- Field `commission_type` (`nominal`/`percentage`) dan `commission_value` dipakai saat hitung komisi di modul Voucher dan Transaksi.
- Belum ada relasi `mitra_id` ke `vouchers`/`services`/`transactions` — ini perlu ditambahkan saat modul Voucher dan Transaksi dibangun penuh (lihat Gap di bagian 7).
- Outlet saat ini disimpan sebagai kolom `outlet_name` di Mitra. Jika satu Mitra perlu banyak outlet, perlu tabel `mitra_outlets` terpisah (out of scope addendum ini, dicatat sebagai gap).

## 2. Menu Voucher (OSS/Radius Hotspot Domain)

| Sub-menu | Fungsi | Status |
|---|---|---|
| Profile voucher | Master profile harga jual voucher: nama, mikrotik group, rate limit, shared user, kuota, durasi, HPP, komisi, harga | `Skeleton` (`VoucherProfiles` page) |
| Stok voucher | Daftar voucher yang sudah dibuat/dicetak tapi belum terjual: username/password, profile, router, server, partner, outlet, kode batch | `Skeleton` (`VoucherStock` page) |
| Voucher terjual | Daftar voucher yang sudah terjual: durasi, kuota, tanggal aktif/expired, MAC address pemakai | `Skeleton` (`SoldVouchers` page) |
| Voucher online | Sesi voucher yang sedang aktif (live dari RADIUS accounting): IP, MAC, uptime, upload/download | `Skeleton` (`OnlineVouchers` page) |
| Rekap voucher | Rekap penjualan per kode batch & tanggal: qty, sisa stok, terjual, total HPP/komisi/harga | `Skeleton` (`VoucherRecap` page) |
| Template voucher | Editor template HTML untuk cetak kartu voucher (header/row/footer + daftar parameter `#username#`, `#password#`, dst.) | `Skeleton` (`VoucherTemplate` page) |

Gap untuk membuat modul ini `Built` penuh:

- Tabel `voucher_profiles` (mirip `radius_profiles` + `products` tapi khusus prabayar: kuota, durasi aktif, mikrotik group/rate limit).
- Tabel `voucher_batches` (kode generate, qty, profile_id, mitra_id, outlet, tanggal cetak).
- Tabel `vouchers` (username, password, batch_id, status `stock`/`sold`/`expired`, sold_at, activated_at, expired_at, mac_address).
- Tabel `voucher_templates` (header/row/footer html, parameter binding).
- Endpoint job untuk generate username/password voucher secara batch dan push ke Mikrotik hotspot user profile.
- Sesi "Voucher online" memerlukan baca live data dari FreeRadius accounting (`radacct`) atau API Mikrotik active hotspot user, bukan tabel sendiri.

## 3. Menu Langganan (Service Domain — sudah ada, addendum penamaan)

| Sub-menu | Pemetaan ke resource existing |
|---|---|
| Profile langganan | `ProductResource` (Profil Langganan) |
| Data berlangganan | `ServiceResource` (Data Berlangganan) |
| Stop berlangganan | `StoppedSubscriptionResource` |
| Langganan online | `OnlineSubscriptionResource` |

Tidak ada gap baru di sini — sudah `Built`, hanya dikonfirmasi pemetaan menu agar konsisten dengan referensi.

## 4. Menu Map (GIS Network Domain)

| Sub-menu | Fungsi | Status |
|---|---|---|
| Map Pelanggan | Peta lokasi pelanggan dari `latitude`/`longitude` di Service, warna pin per status layanan | `Planned` (`MapCustomers` page) |
| Map ODP | Peta lokasi ODP dengan indikator okupansi port | `Planned` (`MapOdp` page) |

Gap: butuh tabel `odps` (lihat bagian 5) sebelum Map ODP bisa menampilkan data nyata. Map Pelanggan secara teknis bisa langsung query `services` yang sudah punya `latitude`/`longitude`.

## 5. Menu ODP, OLT, GenieACS (GIS Network Domain — perluasan FTTH)

| Item | Fungsi | Status |
|---|---|---|
| ODP | Inventaris Optical Distribution Point: kode, merk, ODC/port induk, jumlah port, terisi/kosong, wilayah, lokasi terpasang | `Planned` (`Odp` page) |
| OLT | Inventaris Optical Line Terminal: nama, vendor, model, jenis, IP remote, port, SNMP community read/write | `Planned` (`Olt` page) |
| GenieACS | Integrasi TR-069 ACS untuk monitoring/manajemen ONU pelanggan: status online/offline, RX power, SSID, summon/reboot/reset, multi server ACS | `Planned` (`GenieAcs` page) |

Gap untuk `Built`:

- Tabel `odps` (kode, merk, odc_port, jml_port, wilayah, lokasi, latitude/longitude).
- Tabel `olts` (nama, vendor, model, jenis, ip_remote, port, community_read, community_write, script).
- Tabel `acs_servers` (nama, ip_address, port, script) dan tabel `onu_devices` (flag, username, ip_pppoe, ip_tr069, sn_onu, pon, manufaktur, type, ssid, rx_power, uptime, last_inform) atau, lebih disarankan, panggil GenieACS NBI API langsung tanpa duplikasi data (sinkron on-demand).
- Relasi opsional `service.odp_id` agar service tracking ODP/port konsisten dengan field `odp_number`/`odp_port` yang sudah ada di `ServiceResource`.

## 6. Menu Tiket (Work Order / Field Service Domain)

| Item | Fungsi | Status |
|---|---|---|
| Tiket | CRUD komplain/tiket pelanggan: subjek, deskripsi, status (baru/dibuka/diproses/selesai/ditutup), terkait customer/service/router | `Built` (`TicketResource`, tabel `tickets` sudah ada sejak migration awal) |

Catatan: tabel `tickets` dan `work_orders` sudah ada di schema sejak awal tapi belum punya Filament Resource. Addendum ini menambahkan `TicketResource`. `WorkOrder` (Field Service / instalasi teknisi) belum punya Resource — dicatat sebagai gap lanjutan, prioritas menyusul karena tidak ada di referensi menu yang dilampirkan user.

## 7. Menu Pengaturan: WhatsApp, Tools, Admin, Setting (SaaS Platform & NOC Domain)

| Item | Fungsi | Status |
|---|---|---|
| WhatsApp | Konfigurasi gateway WhatsApp untuk notifikasi (invoice baru, reminder jatuh tempo, isolir, pembayaran diterima) + template pesan + log kirim | `Planned` (`WhatsappSetting` page) |
| Tools | Utility jaringan, contoh: Hurricane Electric BGP Toolkit (AS Number -> Mikrotik address-list script) untuk policy based routing | `Planned` (`ToolsPage` page) |
| Admin | CRUD user staff/admin internal + assign role/permission, terhubung ke model `Role`/`Permission` yang sudah ada di database | `Planned` (`AdminUsers` page) |
| Setting | Branding aplikasi (logo/favicon/nama perusahaan), tampilan invoice (kop, watermark, tanda tangan, catatan paid/unpaid), custom domain (Cloudflare), integrasi rekonsiliasi pembayaran (MutasiBank.co.id, Moota.co), payment gateway | `Planned` (`AppSetting` page) |

Gap untuk `Built`:

- Tabel `whatsapp_gateways` (tenant_id, provider, device/api credential) dan `whatsapp_message_templates`.
- Tabel `network_tools_logs` opsional untuk audit pemakaian BGP toolkit.
- `Admin` cukup memanfaatkan `User`, `Role`, `Permission` yang sudah ada — perlu Filament Resource + form assign role.
- `Setting` perlu tabel/`tenants` kolom tambahan untuk branding lanjutan (favicon, kop invoice, watermark, tanda tangan, custom domain, kredensial MutasiBank/Moota/payment gateway) — sebagian sudah ada (`logo_path`, `billing_settings` di Tenant), sisanya perlu kolom/tabel baru.

## 8. Ringkasan Status Build

| Modul | Status |
|---|---|
| Mitra | `Built` |
| Tiket | `Built` |
| Voucher (6 sub-menu) | `Skeleton` |
| Langganan (4 sub-menu) | `Built` (sudah ada sebelumnya) |
| Map Pelanggan, Map ODP | `Planned` |
| ODP, OLT, GenieACS | `Planned` |
| WhatsApp, Tools, Admin, Setting | `Planned` |

## 9. Urutan Pembangunan yang Disarankan

1. Tambahkan relasi `mitra_id` ke Voucher (setelah tabel voucher dibuat) dan ke Service/Transaksi jika reseller juga menjual layanan reguler.
2. Bangun tabel `voucher_profiles`, `voucher_batches`, `vouchers`, `voucher_templates` agar modul Voucher naik dari `Skeleton` ke `Built`.
3. Bangun `odps` dan `olts` sebagai data master jaringan FTTH, lalu integrasikan ke Map ODP.
4. Integrasikan GenieACS NBI API (server eksternal, bukan duplikasi data) untuk modul GenieACS.
5. Tambahkan `Admin` (role/permission UI) karena modelnya sudah ada — biaya pembangunan rendah.
6. Tambahkan `Setting` branding lanjutan dan `WhatsApp` gateway sesuai kebutuhan notifikasi billing yang sudah berjalan di Domain BSS/Billing.
