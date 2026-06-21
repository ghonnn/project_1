# Rekomendasi Menu Panel NEX ISP Platform

Dokumen ini memetakan rekomendasi menu berdasarkan kode yang sudah ada saat ini.

## URL Panel

| Panel | URL | Role |
|---|---|---|
| Apps Owner | `/owner` | `platform_owner` |
| Admin ISP / Operator | `/isp` | `tenant_owner`, `tenant_admin`, `finance`, `noc`, `sales`, `technician` |
| Pelanggan | `/customer` | `customer` |
| Legacy Admin | `/admin` | Internal kompatibilitas sementara |

## Panel Apps Owner

Tujuan: mengelola platform SaaS secara global.

Rekomendasi menu:

- Dashboard Platform
- Tenant
- Admin & Staff
- Log Audit
- Pengaturan Aplikasi

Fitur berikutnya:

- License usage per tenant
- Tenant billing
- White label/domain tenant
- Marketplace connector management
- Global incident/health overview

## Panel Admin ISP / Operator

Tujuan: operasional ISP sehari-hari.

Rekomendasi menu dari kode yang sudah ada:

- Dashboard
- Transaksi
- Pelanggan
- Partner
- Profile Langganan
- Data Berlangganan
- Langganan Online
- Stop Berlangganan
- Kategori Layanan
- Voucher
- Router
- Interface Router
- ODP
- OLT
- GenieACS
- Script Templates
- Generator Script
- Server Radius
- NAS Device
- Profil Radius
- Pengguna Radius
- Map Pelanggan
- Map ODP
- Tiket
- Invoice Unpaid
- Invoice Paid
- Pembayaran
- Mutasi Saldo
- TopUp Saldo
- Kupon Diskon
- WhatsApp
- Tools
- Setting

Role detail yang disarankan:

- `tenant_owner`: semua menu ISP.
- `tenant_admin`: semua menu operasional kecuali setting sensitif.
- `finance`: Billing, Pembayaran, Invoice, Mutasi Saldo, Kupon Diskon.
- `noc`: Jaringan, Radius, Map, Tiket.
- `sales`: Pelanggan, Partner, Data Berlangganan, Tiket.
- `technician`: Tiket, Map, ODP/OLT/GenieACS read/update terbatas.

## Panel Pelanggan

Tujuan: self-service pelanggan.

Menu MVP yang sudah dibuat:

- Dashboard Pelanggan
- Ringkasan profil pelanggan
- Layanan saya
- Invoice terakhir

Fitur berikutnya:

- Detail tagihan dan download invoice
- Upload bukti bayar
- Riwayat pembayaran
- Buat tiket gangguan
- Status koneksi online/offline
- Data PPPoE/Hotspot read-only
- Notifikasi isolir/jatuh tempo

## Catatan Implementasi Saat Ini

- Panel pelanggan saat ini mencocokkan user login ke pelanggan dari `users.email = customers.email`.
- Relasi eksplisit `customers.user_id` direkomendasikan pada fase berikutnya agar akses pelanggan lebih kuat.
- Panel `/admin` masih dipertahankan untuk kompatibilitas. Setelah `/owner` dan `/isp` stabil, `/admin` bisa dialihkan atau dibatasi hanya untuk platform owner.
