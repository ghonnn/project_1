-- NEX OSS/BSS v3.4 core seed data for local/dev/test
-- UUIDs are deterministic to make API tests stable.

INSERT INTO tenants (id, name, org_number, plan, status)
VALUES
  ('00000000-0000-4000-8000-000000000001', 'PT NEX Solusi Teknologi', 'NEX-001', 'enterprise', 'active'),
  ('00000000-0000-4000-8000-000000000002', 'Demo ISP Tenant', 'DEMO-ISP-001', 'starter', 'active')
ON CONFLICT (id) DO NOTHING;

INSERT INTO permissions (id, code, module, action, description)
VALUES
  ('10000000-0000-4000-8000-000000000001', 'tenant.manage', 'tenant', 'manage', 'Manage tenant settings and lifecycle'),
  ('10000000-0000-4000-8000-000000000002', 'customer.manage', 'customer', 'manage', 'Create, read, update, deactivate customers'),
  ('10000000-0000-4000-8000-000000000003', 'service.manage', 'service', 'manage', 'Manage service lifecycle'),
  ('10000000-0000-4000-8000-000000000004', 'billing.manage', 'billing', 'manage', 'Generate and manage invoices'),
  ('10000000-0000-4000-8000-000000000005', 'payment.manage', 'payment', 'manage', 'Record and reconcile payments'),
  ('10000000-0000-4000-8000-000000000006', 'router.manage', 'router', 'manage', 'Manage routers'),
  ('10000000-0000-4000-8000-000000000007', 'router_interface.manage', 'router_interface', 'manage', 'Manage router interfaces'),
  ('10000000-0000-4000-8000-000000000008', 'router_link.manage', 'router_link', 'manage', 'Manage router links'),
  ('10000000-0000-4000-8000-000000000009', 'snmp.read', 'snmp', 'read', 'Read SNMP monitoring status'),
  ('10000000-0000-4000-8000-000000000010', 'capacity.read', 'capacity', 'read', 'Read router capacity dashboard'),
  ('10000000-0000-4000-8000-000000000011', 'impact.read', 'impact', 'read', 'Run customer impact analysis'),
  ('10000000-0000-4000-8000-000000000012', 'router_script.generate', 'router_script', 'generate', 'Generate RouterOS scripts'),
  ('10000000-0000-4000-8000-000000000013', 'radius.manage', 'radius', 'manage', 'Manage Radius profiles, users, and NAS'),
  ('10000000-0000-4000-8000-000000000014', 'ticket.manage', 'ticket', 'manage', 'Manage tickets'),
  ('10000000-0000-4000-8000-000000000015', 'work_order.manage', 'work_order', 'manage', 'Manage work orders'),
  ('10000000-0000-4000-8000-000000000016', 'audit.read', 'audit', 'read', 'Read audit logs'),
  ('10000000-0000-4000-8000-000000000017', 'report.read', 'report', 'read', 'Read reports')
ON CONFLICT (code) DO NOTHING;

INSERT INTO roles (id, tenant_id, code, name, description, is_system)
VALUES
  ('20000000-0000-4000-8000-000000000001', NULL, 'platform_owner', 'Platform Owner', 'NEX global platform owner', true),
  ('20000000-0000-4000-8000-000000000002', '00000000-0000-4000-8000-000000000002', 'tenant_owner', 'Tenant Owner', 'Tenant ISP owner', true),
  ('20000000-0000-4000-8000-000000000003', '00000000-0000-4000-8000-000000000002', 'tenant_admin', 'Tenant Admin', 'Tenant administrator', true),
  ('20000000-0000-4000-8000-000000000004', '00000000-0000-4000-8000-000000000002', 'finance', 'Finance', 'Finance operator', true),
  ('20000000-0000-4000-8000-000000000005', '00000000-0000-4000-8000-000000000002', 'noc', 'NOC Technician', 'NOC and provisioning operator', true),
  ('20000000-0000-4000-8000-000000000006', '00000000-0000-4000-8000-000000000002', 'sales', 'Sales', 'Sales and CRM operator', true),
  ('20000000-0000-4000-8000-000000000007', '00000000-0000-4000-8000-000000000002', 'field_engineer', 'Field Engineer', 'Technician / field engineer', true),
  ('20000000-0000-4000-8000-000000000008', '00000000-0000-4000-8000-000000000002', 'partner', 'Partner', 'Partner or reseller', true),
  ('20000000-0000-4000-8000-000000000009', '00000000-0000-4000-8000-000000000002', 'customer', 'Customer', 'Customer portal user', true)
ON CONFLICT (tenant_id, code) DO NOTHING;

INSERT INTO role_permissions (role_id, permission_id)
SELECT '20000000-0000-4000-8000-000000000001'::uuid, id FROM permissions
ON CONFLICT DO NOTHING;

INSERT INTO role_permissions (role_id, permission_id)
SELECT '20000000-0000-4000-8000-000000000002'::uuid, id FROM permissions
ON CONFLICT DO NOTHING;

INSERT INTO role_permissions (role_id, permission_id)
SELECT '20000000-0000-4000-8000-000000000003'::uuid, id FROM permissions
WHERE code IN ('customer.manage', 'service.manage', 'router.manage', 'router_interface.manage', 'router_link.manage', 'snmp.read', 'capacity.read', 'impact.read', 'router_script.generate', 'radius.manage', 'ticket.manage', 'work_order.manage', 'audit.read', 'report.read')
ON CONFLICT DO NOTHING;

INSERT INTO role_permissions (role_id, permission_id)
SELECT '20000000-0000-4000-8000-000000000004'::uuid, id FROM permissions
WHERE code IN ('billing.manage', 'payment.manage', 'capacity.read', 'impact.read', 'report.read')
ON CONFLICT DO NOTHING;

INSERT INTO role_permissions (role_id, permission_id)
SELECT '20000000-0000-4000-8000-000000000005'::uuid, id FROM permissions
WHERE code IN ('service.manage', 'router.manage', 'router_interface.manage', 'router_link.manage', 'snmp.read', 'capacity.read', 'impact.read', 'router_script.generate', 'radius.manage', 'ticket.manage', 'work_order.manage', 'report.read')
ON CONFLICT DO NOTHING;

INSERT INTO service_categories (id, tenant_id, code, name, requires_router_mapping, requires_radius, requires_ip, requires_vlan)
VALUES
  ('30000000-0000-4000-8000-000000000001', '00000000-0000-4000-8000-000000000002', 'internet_pppoe', 'Internet PPPoE', true, true, true, true),
  ('30000000-0000-4000-8000-000000000002', '00000000-0000-4000-8000-000000000002', 'internet_hotspot', 'Internet Hotspot', true, true, true, false),
  ('30000000-0000-4000-8000-000000000003', '00000000-0000-4000-8000-000000000002', 'dedicated_internet', 'Dedicated Internet', true, false, true, true),
  ('30000000-0000-4000-8000-000000000004', '00000000-0000-4000-8000-000000000002', 'cloud_hosting', 'Cloud Hosting', false, false, false, false),
  ('30000000-0000-4000-8000-000000000005', '00000000-0000-4000-8000-000000000002', 'domain_ssl', 'Domain and SSL', false, false, false, false)
ON CONFLICT (tenant_id, code) DO NOTHING;

INSERT INTO products (id, tenant_id, service_category_id, sku, name, description, pricing)
VALUES
  ('40000000-0000-4000-8000-000000000001', '00000000-0000-4000-8000-000000000002', '30000000-0000-4000-8000-000000000001', 'INET-PPPOE-50M', 'Internet PPPoE 50 Mbps', 'Residential PPPoE package', '{"recurring":{"amount":250000,"period":"month"},"currency":"IDR"}'),
  ('40000000-0000-4000-8000-000000000002', '00000000-0000-4000-8000-000000000002', '30000000-0000-4000-8000-000000000001', 'INET-PPPOE-100M', 'Internet PPPoE 100 Mbps', 'Residential PPPoE package', '{"recurring":{"amount":400000,"period":"month"},"currency":"IDR"}'),
  ('40000000-0000-4000-8000-000000000003', '00000000-0000-4000-8000-000000000002', '30000000-0000-4000-8000-000000000002', 'HOTSPOT-20M', 'Hotspot 20 Mbps', 'Hotspot access package', '{"recurring":{"amount":150000,"period":"month"},"currency":"IDR"}'),
  ('40000000-0000-4000-8000-000000000004', '00000000-0000-4000-8000-000000000002', '30000000-0000-4000-8000-000000000004', 'CLOUD-BASIC', 'Cloud Hosting Basic', 'Non-network cloud hosting service', '{"recurring":{"amount":100000,"period":"month"},"currency":"IDR"}')
ON CONFLICT (tenant_id, sku) DO NOTHING;

INSERT INTO radius_profiles (id, tenant_id, name, attributes)
VALUES
  ('50000000-0000-4000-8000-000000000001', '00000000-0000-4000-8000-000000000002', 'PPPoE 50M', '{"Mikrotik-Rate-Limit":"50M/50M"}'),
  ('50000000-0000-4000-8000-000000000002', '00000000-0000-4000-8000-000000000002', 'PPPoE 100M', '{"Mikrotik-Rate-Limit":"100M/100M"}'),
  ('50000000-0000-4000-8000-000000000003', '00000000-0000-4000-8000-000000000002', 'Hotspot 20M', '{"Mikrotik-Rate-Limit":"20M/20M"}')
ON CONFLICT (tenant_id, name) DO NOTHING;

INSERT INTO router_script_templates (id, tenant_id, vendor, os_version, script_type, template_body, variables_schema)
VALUES
  ('60000000-0000-4000-8000-000000000001', '00000000-0000-4000-8000-000000000002', 'mikrotik', 'ROS6', 'PPPoE', '/radius add service=ppp address={{radius_server_ip}} secret={{radius_secret}} authentication-port=1812 accounting-port=1813', '{"required":["radius_server_ip","radius_secret"]}'),
  ('60000000-0000-4000-8000-000000000002', '00000000-0000-4000-8000-000000000002', 'mikrotik', 'ROS7', 'PPPoE', '/radius add service=ppp address={{radius_server_ip}} secret={{radius_secret}} authentication-port=1812 accounting-port=1813', '{"required":["radius_server_ip","radius_secret"]}'),
  ('60000000-0000-4000-8000-000000000003', '00000000-0000-4000-8000-000000000002', 'mikrotik', 'ROS6', 'Hotspot', '/radius add service=hotspot address={{radius_server_ip}} secret={{radius_secret}} authentication-port=1812 accounting-port=1813', '{"required":["radius_server_ip","radius_secret"]}'),
  ('60000000-0000-4000-8000-000000000004', '00000000-0000-4000-8000-000000000002', 'mikrotik', 'ROS7', 'Hotspot', '/radius add service=hotspot address={{radius_server_ip}} secret={{radius_secret}} authentication-port=1812 accounting-port=1813', '{"required":["radius_server_ip","radius_secret"]}')
ON CONFLICT (tenant_id, vendor, os_version, script_type) DO NOTHING;

INSERT INTO routers (id, tenant_id, router_name, hostname, vendor, model, router_role, site_name, management_ip, status, snmp_status)
VALUES
  ('70000000-0000-4000-8000-000000000001', '00000000-0000-4000-8000-000000000002', 'BNG Jakarta 01', 'bng-jkt-01', 'MikroTik', 'CCR', 'bng', 'Jakarta POP', '10.0.0.1', 'active', 'reachable'),
  ('70000000-0000-4000-8000-000000000002', '00000000-0000-4000-8000-000000000002', 'POP Bandung 01', 'pop-bdg-01', 'MikroTik', 'CCR', 'pop_router', 'Bandung POP', '10.0.1.1', 'active', 'reachable')
ON CONFLICT (tenant_id, hostname) DO NOTHING;

INSERT INTO router_interfaces (id, tenant_id, router_id, interface_name, interface_type, ip_address, vlan_id, speed_mbps, status)
VALUES
  ('71000000-0000-4000-8000-000000000001', '00000000-0000-4000-8000-000000000002', '70000000-0000-4000-8000-000000000001', 'ether1', 'ethernet', '10.0.0.2', 100, 1000, 'up'),
  ('71000000-0000-4000-8000-000000000002', '00000000-0000-4000-8000-000000000002', '70000000-0000-4000-8000-000000000002', 'ether1', 'ethernet', '10.0.1.2', 200, 1000, 'up')
ON CONFLICT (tenant_id, router_id, interface_name) DO NOTHING;

INSERT INTO nas_devices (id, tenant_id, router_id, hostname, nas_ip_address, vendor_type, secret, status)
VALUES
  ('72000000-0000-4000-8000-000000000001', '00000000-0000-4000-8000-000000000002', '70000000-0000-4000-8000-000000000001', 'bng-jkt-01', '10.0.0.1', 'mikrotik', 'encrypted:change-me', 'active')
ON CONFLICT (tenant_id, nas_ip_address) DO NOTHING;
