# PRD NEX OSS/BSS - Role Permission Matrix v3.4 Router-Centric

Dokumen ini merevisi Step 3 agar seluruh permission mengikuti PRD v3.4 Router-Centric.

Legenda:

- Y = full allowed
- R = tenant-scoped/restricted
- S = self-only
- N = no access

Tenant-scoped roles hanya boleh membaca/mengubah data dengan `tenant_id` yang sama. Platform Owner dapat mengelola lintas tenant.

## Gap Analysis v3.4

| Area | Gap Lama | Revisi v3.4 |
|---|---|---|
| Network permission | Belum ada Router Management lengkap | Ditambahkan Router, Interface, Link, SNMP, Capacity, Impact, Script Generator |
| Role NOC | Akses router belum eksplisit | NOC mendapat akses operasional router dan monitoring |
| POP/BTS | Berpotensi menjadi permission terpisah | Tidak ada permission POP/BTS; gunakan Router Role |
| Impact | Belum ada izin customer impact | Ditambahkan Customer Impact Analysis |

## Matrix

| Module | Action/Permission | Platform Owner | Tenant Owner | Tenant Admin | Finance | NOC | Sales | Teknisi | Partner | Customer |
|---|---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| Customer | Create customer | Y | R | R | N | N | R | N | R | N |
| Customer | Read customer | Y | R | R | R | R | R | R | R | S |
| Customer | Update customer | Y | R | R | N | N | R | N | R | S |
| Customer | Delete customer | Y | R | N | N | N | N | N | N | N |
| Service | Create service | Y | R | R | N | N | R | R | R | N |
| Service | Read service | Y | R | R | R | R | R | R | R | S |
| Service | Update service | Y | R | R | N | R | N | R | N | N |
| Service | Suspend/unsuspend service | Y | R | R | N | R | N | N | N | N |
| Router Management | Create router | Y | R | R | N | R | N | N | N | N |
| Router Management | Read router | Y | R | R | N | R | N | R | N | N |
| Router Management | Update router | Y | R | R | N | R | N | N | N | N |
| Router Management | Retire/deactivate router | Y | R | R | N | R | N | N | N | N |
| Router Interface Management | Create interface | Y | R | R | N | R | N | N | N | N |
| Router Interface Management | Read interface | Y | R | R | N | R | N | R | N | N |
| Router Interface Management | Update interface | Y | R | R | N | R | N | N | N | N |
| Router Link Management | Create router link | Y | R | R | N | R | N | N | N | N |
| Router Link Management | Read router link | Y | R | R | N | R | N | R | N | N |
| Router Link Management | Update router link | Y | R | R | N | R | N | N | N | N |
| SNMP Monitoring | Configure SNMP | Y | R | R | N | R | N | N | N | N |
| SNMP Monitoring | Read SNMP status | Y | R | R | N | R | N | R | N | N |
| Capacity Dashboard | Read capacity | Y | R | R | R | R | N | R | N | N |
| Customer Impact Analysis | Run impact analysis | Y | R | R | R | R | N | R | N | N |
| Router Script Generator | Generate script | Y | R | R | N | R | N | N | N | N |
| Radius | Create profile | Y | R | R | N | R | N | N | N | N |
| Radius | Manage radius user | Y | R | R | N | R | N | R | N | N |
| Radius | Read accounting | Y | R | R | N | R | N | N | N | N |
| Radius | Manage NAS device | Y | R | R | N | R | N | N | N | N |
| Billing | Generate invoice | Y | R | R | R | N | N | N | N | N |
| Billing | Read invoice | Y | R | R | R | N | N | N | R | S |
| Billing | Adjust invoice | Y | R | N | R | N | N | N | N | N |
| Payment | Record payment | Y | R | N | R | N | N | N | N | S |
| Payment | Reconcile payment | Y | R | N | R | N | N | N | N | N |
| Ticket | Create ticket | Y | R | R | N | R | R | R | R | S |
| Ticket | Read ticket | Y | R | R | N | R | R | R | R | S |
| Ticket | Update/resolve ticket | Y | R | R | N | R | N | R | N | N |
| Work Order | Create work order | Y | R | R | N | R | N | R | N | N |
| Work Order | Assign/schedule | Y | R | R | N | R | N | R | N | N |
| Work Order | Execute/report | Y | R | N | N | R | N | R | N | N |
| Contract | Draft contract | Y | R | R | N | N | R | N | R | N |
| Contract | Sign/activate contract | Y | R | R | N | N | R | N | R | N |
| Contract | Read contract | Y | R | R | R | N | R | N | R | S |
| Inventory | Read inventory | Y | R | R | N | R | N | R | R | N |
| Inventory | Update inventory | Y | R | R | N | R | N | R | N | N |
| GIS | View map | Y | R | R | N | R | N | R | R | N |
| GIS | Edit map | Y | R | R | N | R | N | R | N | N |
| Report | Run operational reports | Y | R | R | R | R | R | R | R | N |
| Audit Log | Read audit log | Y | R | R | N | N | N | N | N | N |
| Tenant Setting | Manage tenant settings | Y | R | R | N | N | N | N | N | N |

## Role yang Wajib Memiliki Akses Router-Centric

- Platform Owner: semua permission router, monitoring, impact, script.
- Tenant Owner: semua permission router dalam tenant.
- Tenant Admin: semua permission router operasional dalam tenant.
- NOC Technician/NOC: router management, interface, link, SNMP monitoring, capacity dashboard, customer impact analysis, router script generator.

## Catatan Implementasi

- Tidak ada permission khusus POP/BTS. Gunakan permission Router Management dengan filter `router_role`.
- Aksi generate script wajib masuk audit log karena dapat mengubah konfigurasi router saat diterapkan.
- Finance boleh melihat capacity dan impact untuk estimasi revenue impact, tetapi tidak boleh mengubah router.
- Partner dan Customer tidak boleh melihat detail topologi router tenant.
