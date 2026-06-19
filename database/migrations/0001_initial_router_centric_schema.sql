-- NEX OSS/BSS v3.4 Router-Centric initial schema
-- Target database: PostgreSQL 15+
-- Rule: Customer -> Service -> Router -> Router Interface -> Radius NAS -> FreeRadius

CREATE EXTENSION IF NOT EXISTS pgcrypto;

CREATE TABLE tenants (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  name text NOT NULL,
  org_number text,
  plan text NOT NULL DEFAULT 'starter',
  status text NOT NULL DEFAULT 'active',
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz,
  CONSTRAINT tenants_status_chk CHECK (status IN ('active', 'suspended', 'trial', 'terminated'))
);

CREATE TABLE users (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id uuid REFERENCES tenants(id),
  username text NOT NULL,
  email text NOT NULL,
  full_name text,
  password_hash text,
  is_active boolean NOT NULL DEFAULT true,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz,
  UNIQUE (tenant_id, username),
  UNIQUE (tenant_id, email)
);

CREATE TABLE roles (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id uuid REFERENCES tenants(id),
  code text NOT NULL,
  name text NOT NULL,
  description text,
  is_system boolean NOT NULL DEFAULT false,
  created_at timestamptz NOT NULL DEFAULT now(),
  UNIQUE (tenant_id, code)
);

CREATE TABLE permissions (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  code text NOT NULL UNIQUE,
  module text NOT NULL,
  action text NOT NULL,
  description text
);

CREATE TABLE role_permissions (
  role_id uuid NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
  permission_id uuid NOT NULL REFERENCES permissions(id) ON DELETE CASCADE,
  PRIMARY KEY (role_id, permission_id)
);

CREATE TABLE user_roles (
  user_id uuid NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  role_id uuid NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
  PRIMARY KEY (user_id, role_id)
);

CREATE TABLE customers (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id uuid NOT NULL REFERENCES tenants(id),
  external_ref text,
  type text NOT NULL,
  name text NOT NULL,
  billing_contact jsonb NOT NULL DEFAULT '{}'::jsonb,
  status text NOT NULL DEFAULT 'prospect',
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz,
  deleted_at timestamptz,
  UNIQUE (tenant_id, external_ref),
  CONSTRAINT customers_type_chk CHECK (type IN ('individual', 'business')),
  CONSTRAINT customers_status_chk CHECK (status IN ('prospect', 'lead', 'qualified', 'contracted', 'active', 'suspended', 'terminated'))
);

CREATE TABLE service_categories (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id uuid NOT NULL REFERENCES tenants(id),
  code text NOT NULL,
  name text NOT NULL,
  requires_router_mapping boolean NOT NULL DEFAULT false,
  requires_radius boolean NOT NULL DEFAULT false,
  requires_ip boolean NOT NULL DEFAULT false,
  requires_vlan boolean NOT NULL DEFAULT false,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz,
  UNIQUE (tenant_id, code)
);

CREATE TABLE products (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id uuid NOT NULL REFERENCES tenants(id),
  service_category_id uuid NOT NULL REFERENCES service_categories(id),
  sku text NOT NULL,
  name text NOT NULL,
  description text,
  pricing jsonb NOT NULL DEFAULT '{}'::jsonb,
  is_active boolean NOT NULL DEFAULT true,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz,
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
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz,
  CONSTRAINT contracts_status_chk CHECK (status IN ('draft', 'negotiation', 'signed', 'active', 'renewal_pending', 'expired', 'terminated'))
);

CREATE TABLE services (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id uuid NOT NULL REFERENCES tenants(id),
  customer_id uuid NOT NULL REFERENCES customers(id),
  product_id uuid NOT NULL REFERENCES products(id),
  service_category_id uuid NOT NULL REFERENCES service_categories(id),
  contract_id uuid REFERENCES contracts(id),
  cid text,
  status text NOT NULL DEFAULT 'requested',
  activated_at timestamptz,
  suspended_at timestamptz,
  terminated_at timestamptz,
  metadata jsonb NOT NULL DEFAULT '{}'::jsonb,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz,
  UNIQUE (tenant_id, cid),
  CONSTRAINT services_status_chk CHECK (status IN ('requested', 'provisioning', 'active', 'suspended', 'terminated', 'failed'))
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
  management_ip inet NOT NULL,
  latitude numeric(10,7),
  longitude numeric(10,7),
  status text NOT NULL DEFAULT 'draft',
  snmp_status text NOT NULL DEFAULT 'not_configured',
  snmp_profile jsonb NOT NULL DEFAULT '{}'::jsonb,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz,
  deleted_at timestamptz,
  UNIQUE (tenant_id, hostname),
  CONSTRAINT routers_role_chk CHECK (router_role IN ('core_router', 'aggregation_router', 'edge_router', 'pppoe_router', 'bng', 'wireless_gateway', 'pop_router', 'bts_router')),
  CONSTRAINT routers_status_chk CHECK (status IN ('draft', 'provisioning', 'active', 'warning', 'critical', 'maintenance', 'offline', 'retired')),
  CONSTRAINT routers_snmp_status_chk CHECK (snmp_status IN ('reachable', 'unreachable', 'auth_failed', 'not_configured'))
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
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz,
  UNIQUE (tenant_id, router_id, interface_name),
  CONSTRAINT router_interfaces_status_chk CHECK (status IN ('provisioning', 'up', 'down', 'disabled'))
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
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz,
  CONSTRAINT router_links_distinct_router_chk CHECK (router_a_id <> router_b_id)
);

CREATE TABLE service_router_mapping (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id uuid NOT NULL REFERENCES tenants(id),
  service_id uuid NOT NULL REFERENCES services(id) ON DELETE CASCADE,
  router_id uuid NOT NULL REFERENCES routers(id),
  interface_id uuid REFERENCES router_interfaces(id),
  vlan_id integer,
  is_primary boolean NOT NULL DEFAULT true,
  created_at timestamptz NOT NULL DEFAULT now(),
  UNIQUE (tenant_id, service_id, router_id, interface_id)
);

CREATE TABLE customer_router_mapping (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id uuid NOT NULL REFERENCES tenants(id),
  customer_id uuid NOT NULL REFERENCES customers(id) ON DELETE CASCADE,
  router_id uuid NOT NULL REFERENCES routers(id),
  created_at timestamptz NOT NULL DEFAULT now(),
  UNIQUE (tenant_id, customer_id, router_id)
);

CREATE TABLE radius_profiles (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id uuid NOT NULL REFERENCES tenants(id),
  name text NOT NULL,
  attributes jsonb NOT NULL DEFAULT '{}'::jsonb,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz,
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
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz,
  UNIQUE (tenant_id, nas_ip_address),
  CONSTRAINT nas_devices_status_chk CHECK (status IN ('active', 'inactive', 'maintenance'))
);

CREATE TABLE radius_users (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id uuid NOT NULL REFERENCES tenants(id),
  customer_id uuid NOT NULL REFERENCES customers(id),
  service_id uuid NOT NULL REFERENCES services(id),
  router_id uuid REFERENCES routers(id),
  profile_id uuid NOT NULL REFERENCES radius_profiles(id),
  username text NOT NULL,
  secret text NOT NULL,
  status text NOT NULL DEFAULT 'pending',
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz,
  UNIQUE (tenant_id, username),
  CONSTRAINT radius_users_status_chk CHECK (status IN ('pending', 'active', 'suspended', 'terminated'))
);

CREATE TABLE invoices (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id uuid NOT NULL REFERENCES tenants(id),
  customer_id uuid NOT NULL REFERENCES customers(id),
  contract_id uuid REFERENCES contracts(id),
  invoice_number text NOT NULL,
  issue_date date NOT NULL,
  due_date date NOT NULL,
  status text NOT NULL DEFAULT 'issued',
  total_amount numeric(14,2) NOT NULL DEFAULT 0,
  paid_amount numeric(14,2) NOT NULL DEFAULT 0,
  currency text NOT NULL DEFAULT 'IDR',
  metadata jsonb NOT NULL DEFAULT '{}'::jsonb,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz,
  UNIQUE (tenant_id, invoice_number),
  CONSTRAINT invoices_status_chk CHECK (status IN ('draft', 'issued', 'overdue', 'partial_paid', 'paid', 'written_off', 'cancelled')),
  CONSTRAINT invoices_amount_chk CHECK (total_amount >= 0 AND paid_amount >= 0 AND paid_amount <= total_amount)
);

CREATE TABLE invoice_items (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id uuid NOT NULL REFERENCES tenants(id),
  invoice_id uuid NOT NULL REFERENCES invoices(id) ON DELETE CASCADE,
  service_id uuid REFERENCES services(id),
  description text NOT NULL,
  quantity numeric(12,2) NOT NULL DEFAULT 1,
  unit_amount numeric(14,2) NOT NULL,
  total_amount numeric(14,2) NOT NULL,
  created_at timestamptz NOT NULL DEFAULT now(),
  CONSTRAINT invoice_items_amount_chk CHECK (quantity > 0 AND unit_amount >= 0 AND total_amount >= 0)
);

CREATE TABLE payments (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id uuid NOT NULL REFERENCES tenants(id),
  invoice_id uuid NOT NULL REFERENCES invoices(id),
  external_payment_ref text,
  amount numeric(14,2) NOT NULL,
  currency text NOT NULL DEFAULT 'IDR',
  method text NOT NULL,
  status text NOT NULL DEFAULT 'initiated',
  paid_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz,
  CONSTRAINT payments_amount_chk CHECK (amount > 0),
  CONSTRAINT payments_status_chk CHECK (status IN ('initiated', 'authorized', 'captured', 'reconciled', 'failed', 'refunded', 'cancelled'))
);

CREATE TABLE incidents (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id uuid NOT NULL REFERENCES tenants(id),
  router_id uuid REFERENCES routers(id),
  severity text NOT NULL DEFAULT 'P3',
  status text NOT NULL DEFAULT 'open',
  affected_customers integer NOT NULL DEFAULT 0,
  affected_services integer NOT NULL DEFAULT 0,
  affected_radius_users integer NOT NULL DEFAULT 0,
  revenue_impact_monthly numeric(14,2) NOT NULL DEFAULT 0,
  summary text NOT NULL,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz,
  CONSTRAINT incidents_severity_chk CHECK (severity IN ('P1', 'P2', 'P3', 'P4')),
  CONSTRAINT incidents_status_chk CHECK (status IN ('open', 'triaging', 'in_progress', 'resolved', 'closed'))
);

CREATE TABLE tickets (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id uuid NOT NULL REFERENCES tenants(id),
  customer_id uuid REFERENCES customers(id),
  service_id uuid REFERENCES services(id),
  router_id uuid REFERENCES routers(id),
  incident_id uuid REFERENCES incidents(id),
  created_by uuid REFERENCES users(id),
  assigned_to uuid REFERENCES users(id),
  priority text NOT NULL DEFAULT 'P3',
  category text,
  status text NOT NULL DEFAULT 'new',
  subject text NOT NULL,
  description text,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz,
  CONSTRAINT tickets_status_chk CHECK (status IN ('new', 'acknowledged', 'assigned', 'in_progress', 'resolved', 'awaiting_customer', 'closed', 'escalated'))
);

CREATE TABLE work_orders (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id uuid NOT NULL REFERENCES tenants(id),
  ticket_id uuid REFERENCES tickets(id),
  service_id uuid REFERENCES services(id),
  router_id uuid REFERENCES routers(id),
  interface_id uuid REFERENCES router_interfaces(id),
  router_link_id uuid REFERENCES router_links(id),
  assigned_to uuid REFERENCES users(id),
  scheduled_for timestamptz,
  status text NOT NULL DEFAULT 'created',
  scope text,
  report jsonb NOT NULL DEFAULT '{}'::jsonb,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz,
  CONSTRAINT work_orders_status_chk CHECK (status IN ('created', 'planned', 'scheduled', 'executing', 'completed', 'verified', 'closed', 'cancelled'))
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
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz,
  UNIQUE (tenant_id, vendor, os_version, script_type),
  CONSTRAINT router_script_templates_os_chk CHECK (os_version IN ('ROS6', 'ROS7')),
  CONSTRAINT router_script_templates_type_chk CHECK (script_type IN ('PPPoE', 'Hotspot'))
);

CREATE TABLE audit_logs (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id uuid REFERENCES tenants(id),
  actor_user_id uuid REFERENCES users(id),
  action text NOT NULL,
  target_table text,
  target_id uuid,
  request_id text,
  ip_address inet,
  user_agent text,
  payload jsonb NOT NULL DEFAULT '{}'::jsonb,
  created_at timestamptz NOT NULL DEFAULT now()
);

CREATE INDEX idx_users_tenant ON users(tenant_id);
CREATE INDEX idx_customers_tenant_status ON customers(tenant_id, status);
CREATE INDEX idx_services_tenant_customer_status ON services(tenant_id, customer_id, status);
CREATE INDEX idx_services_category ON services(tenant_id, service_category_id);
CREATE INDEX idx_routers_tenant_role_status ON routers(tenant_id, router_role, status);
CREATE INDEX idx_routers_snmp ON routers(tenant_id, snmp_status);
CREATE INDEX idx_router_interfaces_router ON router_interfaces(tenant_id, router_id);
CREATE INDEX idx_service_router_mapping_router ON service_router_mapping(tenant_id, router_id);
CREATE INDEX idx_service_router_mapping_service ON service_router_mapping(tenant_id, service_id);
CREATE INDEX idx_customer_router_mapping_router ON customer_router_mapping(tenant_id, router_id);
CREATE INDEX idx_radius_users_service ON radius_users(tenant_id, service_id);
CREATE INDEX idx_radius_users_router ON radius_users(tenant_id, router_id);
CREATE INDEX idx_nas_devices_router ON nas_devices(tenant_id, router_id);
CREATE INDEX idx_invoices_customer_status ON invoices(tenant_id, customer_id, status);
CREATE INDEX idx_payments_invoice_status ON payments(tenant_id, invoice_id, status);
CREATE INDEX idx_tickets_router_status ON tickets(tenant_id, router_id, status);
CREATE INDEX idx_router_capacity_history_router_time ON router_capacity_history(tenant_id, router_id, recorded_at DESC);
CREATE INDEX idx_audit_logs_tenant_time ON audit_logs(tenant_id, created_at DESC);

-- Guardrail: table names bts, bts_sites, pop, pop_sites are intentionally absent.
-- POP and BTS are only valid values through routers.router_role: pop_router and bts_router.
