# PRD NEX OSS/BSS - Database Design v3.4 Router-Centric

Peran: Senior Database Architect

Tujuan dokumen ini adalah merevisi desain database Step 2 agar konsisten dengan PRD v3.4 Router-Centric.

Prinsip topologi wajib:

`Customer -> Service -> Router -> Router Interface -> Radius NAS -> FreeRadius`

POP dan BTS tidak boleh menjadi tabel/modul utama. Keduanya hanya direpresentasikan sebagai nilai `router_role` pada tabel `routers`.

## Gap Analysis v3.4

| Area | Gap Lama | Revisi v3.4 |
|---|---|---|
| Router | Belum menjadi entity topology utama | Ditambahkan `routers`, `router_interfaces`, `router_links` |
| Mapping | Radius user hanya terkait service | Ditambahkan service/customer mapping ke router dan interface |
| Capacity | Belum ada histori kapasitas router | Ditambahkan `router_capacity_history` |
| Script | Belum ada template script RouterOS | Ditambahkan `router_script_templates` |
| POP/BTS | Berisiko menjadi tabel terpisah | Dilarang membuat `bts`, `bts_sites`, `pop`, `pop_sites` |

## Tabel Utama

- `tenants`
- `users`
- `roles`
- `permissions`
- `role_permissions`
- `user_roles`
- `customers`
- `products`
- `service_categories`
- `services`
- `contracts`
- `invoices`
- `invoice_items`
- `payments`
- `tickets`
- `work_orders`
- `radius_profiles`
- `radius_users`
- `nas_devices`
- `routers`
- `router_interfaces`
- `router_links`
- `customer_router_mapping`
- `service_router_mapping`
- `router_capacity_history`
- `router_script_templates`
- `audit_logs`

## Tabel yang Dilarang

Tabel berikut tidak boleh dibuat karena digantikan oleh `routers.router_role`:

- `bts`
- `bts_sites`
- `pop`
- `pop_sites`

## Relasi Wajib

- Customer -> Service
- Service -> Router melalui `service_router_mapping`
- Service -> Router Interface melalui `service_router_mapping.interface_id`
- Router -> Radius NAS melalui `nas_devices.router_id`
- Radius User -> Router melalui `radius_users.router_id`
- Radius User -> Service melalui `radius_users.service_id`
- Customer -> Router melalui `customer_router_mapping` untuk query impact cepat

## Enum / Controlled Values

### router_role

- `core_router`
- `aggregation_router`
- `edge_router`
- `pppoe_router`
- `bng`
- `wireless_gateway`
- `pop_router`
- `bts_router`

### router_status

- `draft`
- `provisioning`
- `active`
- `warning`
- `critical`
- `maintenance`
- `offline`
- `retired`

### snmp_status

- `reachable`
- `unreachable`
- `auth_failed`
- `not_configured`

### router_interface_status

- `provisioning`
- `up`
- `down`
- `disabled`

### radius_user_status

- `pending`
- `active`
- `suspended`
- `terminated`

## SQL Skeleton

```sql
CREATE TABLE tenants (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  name text NOT NULL,
  org_number text,
  plan text,
  created_at timestamptz DEFAULT now(),
  updated_at timestamptz
);

CREATE TABLE customers (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id uuid NOT NULL REFERENCES tenants(id),
  external_ref text,
  type text NOT NULL,
  name text NOT NULL,
  billing_contact jsonb,
  status text NOT NULL DEFAULT 'prospect',
  created_at timestamptz DEFAULT now(),
  updated_at timestamptz,
  UNIQUE (tenant_id, external_ref)
);

CREATE TABLE service_categories (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id uuid NOT NULL REFERENCES tenants(id),
  code text NOT NULL,
  name text NOT NULL,
  requires_router_mapping boolean NOT NULL DEFAULT false,
  requires_radius boolean NOT NULL DEFAULT false,
  created_at timestamptz DEFAULT now(),
  UNIQUE (tenant_id, code)
);

CREATE TABLE products (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id uuid NOT NULL REFERENCES tenants(id),
  service_category_id uuid REFERENCES service_categories(id),
  sku text NOT NULL,
  name text NOT NULL,
  pricing jsonb NOT NULL DEFAULT '{}'::jsonb,
  created_at timestamptz DEFAULT now(),
  UNIQUE (tenant_id, sku)
);

CREATE TABLE contracts (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id uuid NOT NULL REFERENCES tenants(id),
  customer_id uuid NOT NULL REFERENCES customers(id),
  status text NOT NULL DEFAULT 'draft',
  start_date date,
  end_date date,
  terms jsonb NOT NULL DEFAULT '{}'::jsonb,
  signed_at timestamptz,
  created_at timestamptz DEFAULT now()
);

CREATE TABLE services (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id uuid NOT NULL REFERENCES tenants(id),
  customer_id uuid NOT NULL REFERENCES customers(id),
  product_id uuid REFERENCES products(id),
  service_category_id uuid REFERENCES service_categories(id),
  contract_id uuid REFERENCES contracts(id),
  cid text,
  status text NOT NULL DEFAULT 'requested',
  activated_at timestamptz,
  suspended_at timestamptz,
  terminated_at timestamptz,
  metadata jsonb NOT NULL DEFAULT '{}'::jsonb,
  created_at timestamptz DEFAULT now(),
  updated_at timestamptz,
  UNIQUE (tenant_id, cid)
);

CREATE TABLE routers (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id uuid NOT NULL REFERENCES tenants(id),
  router_name text NOT NULL,
  hostname text NOT NULL,
  vendor text,
  model text,
  serial_number text,
  router_role text NOT NULL,
  site_name text,
  management_ip inet,
  latitude numeric(10,7),
  longitude numeric(10,7),
  status text NOT NULL DEFAULT 'draft',
  snmp_status text NOT NULL DEFAULT 'not_configured',
  snmp_profile jsonb,
  created_at timestamptz DEFAULT now(),
  updated_at timestamptz,
  deleted_at timestamptz,
  UNIQUE (tenant_id, hostname)
);

CREATE TABLE router_interfaces (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id uuid NOT NULL REFERENCES tenants(id),
  router_id uuid NOT NULL REFERENCES routers(id),
  interface_name text NOT NULL,
  interface_type text,
  mac_address text,
  ip_address inet,
  vlan_id integer,
  speed_mbps integer,
  status text NOT NULL DEFAULT 'provisioning',
  created_at timestamptz DEFAULT now(),
  updated_at timestamptz,
  UNIQUE (tenant_id, router_id, interface_name)
);

CREATE TABLE router_links (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id uuid NOT NULL REFERENCES tenants(id),
  router_a_id uuid NOT NULL REFERENCES routers(id),
  router_b_id uuid NOT NULL REFERENCES routers(id),
  interface_a_id uuid REFERENCES router_interfaces(id),
  interface_b_id uuid REFERENCES router_interfaces(id),
  link_type text,
  status text NOT NULL DEFAULT 'active',
  capacity_mbps integer,
  created_at timestamptz DEFAULT now()
);

CREATE TABLE service_router_mapping (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id uuid NOT NULL REFERENCES tenants(id),
  service_id uuid NOT NULL REFERENCES services(id),
  router_id uuid NOT NULL REFERENCES routers(id),
  interface_id uuid REFERENCES router_interfaces(id),
  vlan_id integer,
  is_primary boolean NOT NULL DEFAULT true,
  created_at timestamptz DEFAULT now(),
  UNIQUE (tenant_id, service_id, router_id, interface_id)
);

CREATE TABLE customer_router_mapping (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id uuid NOT NULL REFERENCES tenants(id),
  customer_id uuid NOT NULL REFERENCES customers(id),
  router_id uuid NOT NULL REFERENCES routers(id),
  created_at timestamptz DEFAULT now(),
  UNIQUE (tenant_id, customer_id, router_id)
);

CREATE TABLE radius_profiles (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id uuid NOT NULL REFERENCES tenants(id),
  name text NOT NULL,
  attributes jsonb NOT NULL DEFAULT '{}'::jsonb,
  created_at timestamptz DEFAULT now(),
  UNIQUE (tenant_id, name)
);

CREATE TABLE nas_devices (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id uuid NOT NULL REFERENCES tenants(id),
  router_id uuid NOT NULL REFERENCES routers(id),
  hostname text NOT NULL,
  nas_ip_address inet NOT NULL,
  vendor_type text,
  secret text NOT NULL,
  status text NOT NULL DEFAULT 'active',
  created_at timestamptz DEFAULT now(),
  UNIQUE (tenant_id, nas_ip_address)
);

CREATE TABLE radius_users (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id uuid NOT NULL REFERENCES tenants(id),
  customer_id uuid NOT NULL REFERENCES customers(id),
  service_id uuid NOT NULL REFERENCES services(id),
  router_id uuid REFERENCES routers(id),
  profile_id uuid REFERENCES radius_profiles(id),
  username text NOT NULL,
  secret text NOT NULL,
  status text NOT NULL DEFAULT 'pending',
  created_at timestamptz DEFAULT now(),
  updated_at timestamptz,
  UNIQUE (tenant_id, username)
);

CREATE TABLE router_capacity_history (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id uuid NOT NULL REFERENCES tenants(id),
  router_id uuid NOT NULL REFERENCES routers(id),
  cpu_percent numeric(5,2),
  memory_percent numeric(5,2),
  traffic_in_bps bigint,
  traffic_out_bps bigint,
  utilization_percent numeric(5,2),
  recorded_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE router_script_templates (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id uuid REFERENCES tenants(id),
  vendor text NOT NULL DEFAULT 'mikrotik',
  os_version text NOT NULL,
  script_type text NOT NULL,
  template_body text NOT NULL,
  variables_schema jsonb NOT NULL DEFAULT '{}'::jsonb,
  is_active boolean NOT NULL DEFAULT true,
  created_at timestamptz DEFAULT now(),
  UNIQUE (tenant_id, vendor, os_version, script_type)
);
```

## ERD Teks

```text
tenants
  +-- customers
  |     +-- services
  |           +-- service_router_mapping -- routers -- router_interfaces
  |           +-- radius_users -----------+
  |
  +-- routers
  |     +-- router_interfaces
  |     +-- router_links
  |     +-- nas_devices
  |     +-- router_capacity_history
  |
  +-- customer_router_mapping
  +-- radius_profiles
  +-- router_script_templates
```

## Index dan Constraint Penting

- `services(tenant_id, customer_id, status)`
- `service_router_mapping(tenant_id, router_id)`
- `customer_router_mapping(tenant_id, router_id)`
- `radius_users(tenant_id, service_id)`
- `radius_users(tenant_id, router_id)`
- `router_capacity_history(tenant_id, router_id, recorded_at DESC)`
- `routers(tenant_id, router_role, status)`

Constraint aplikasi:

- Jika `service_categories.requires_router_mapping = true`, service aktif wajib memiliki `service_router_mapping`.
- Jika `service_categories.requires_radius = true`, service aktif wajib memiliki `radius_users`.
- Jika radius user dibuat untuk layanan jaringan, `radius_users.router_id` wajib terisi.
- `nas_devices.router_id` wajib mengacu ke router yang berperan sebagai NAS/BNG/PPPoE/Hotspot gateway.

## Risiko dan Mitigasi

- Cross-tenant leakage: wajib RLS atau middleware tenant enforcement.
- Secret Radius/NAS bocor: simpan terenkripsi via KMS, jangan masuk log/plain backup.
- Impact query lambat: gunakan mapping table dan index router/customer/service.
- Data topology tidak sinkron: semua perubahan router/interface/mapping harus menghasilkan audit log dan event.

## QC Checklist

- `routers`, `router_interfaces`, `router_links`, `customer_router_mapping`, `service_router_mapping`, `router_capacity_history`, dan `router_script_templates` tersedia.
- Tidak ada tabel `bts`, `bts_sites`, `pop`, atau `pop_sites`.
- Customer tidak terhubung langsung ke POP/BTS.
- Service Internet wajib dapat ditelusuri ke router dan interface.
- Radius NAS dan Radius User dapat ditelusuri ke router.
