# NEX OSS/BSS v3.4 - Minimal MVP Test Scenarios

Dokumen ini mengunci test scenario minimal sebelum coding dimulai.

## Test Data Baseline

Use seed:

- Tenant A: `Demo ISP Tenant`
- Tenant B: create additional tenant in test.
- Roles: Platform Owner, Tenant Owner, Tenant Admin, Finance, NOC, Sales, Field Engineer, Partner, Customer.
- Service categories:
  - `internet_pppoe` requires router mapping and Radius.
  - `internet_hotspot` requires router mapping and Radius.
  - `dedicated_internet` requires router mapping but not Radius.
  - `cloud_hosting` does not require router mapping or Radius.

## 1. Tenant Isolation

### TI-001 Tenant user cannot list another tenant customers

Steps:

1. Login as Tenant A admin.
2. Call `GET /tenants/{tenant_b}/customers`.

Expected:

- HTTP 403 or 404.
- No Tenant B data leaked.

### TI-002 Tenant user cannot reference router from another tenant

Steps:

1. Login as Tenant A NOC.
2. Create Service under Tenant A.
3. Try mapping service to Tenant B router.

Expected:

- HTTP 404 or 409.
- Mapping not created.

### TI-003 Platform Owner can access multiple tenants

Steps:

1. Login as Platform Owner.
2. List tenants.
3. Read Tenant A router and Tenant B router.

Expected:

- Access allowed.
- Audit log records platform-level access if sensitive.

## 2. Billing

### BILL-001 Create invoice for active service

Steps:

1. Create customer.
2. Create non-network service.
3. Activate service.
4. Create invoice for service.

Expected:

- Invoice status `issued`.
- Invoice item references service.
- Total amount equals item total.

### BILL-002 Reject invoice for terminated service

Steps:

1. Create terminated service.
2. Create invoice item referencing terminated service.

Expected:

- HTTP 409 `business_rule_conflict`.

### BILL-003 Reconcile payment

Steps:

1. Create issued invoice.
2. Create payment.
3. Reconcile payment.

Expected:

- Payment status `reconciled`.
- Invoice paid amount updated.
- Invoice status `paid` if fully paid.

## 3. Service Activation

### SA-001 Internet service cannot activate without router mapping

Steps:

1. Create customer.
2. Create product with category `internet_pppoe`.
3. Create service.
4. Call activate endpoint.

Expected:

- HTTP 409.
- Error says router mapping required.
- Service remains `requested` or `provisioning`.

### SA-002 Non-network service can activate without router mapping

Steps:

1. Create product with category `cloud_hosting`.
2. Create service.
3. Activate service.

Expected:

- HTTP 200.
- Service status `active`.
- No router mapping required.

### SA-003 Internet service activates after router and Radius ready

Steps:

1. Create router.
2. Create router interface.
3. Map service to router/interface.
4. Create Radius profile.
5. Create Radius user.
6. Activate service.

Expected:

- Service status `active`.
- Radius user status `active` or queued to activate depending adapter mode.
- Audit log recorded.

## 4. Router Mapping

### RM-001 Create valid service router mapping

Steps:

1. Create router in same tenant.
2. Create interface under router.
3. Create service.
4. Map service to router/interface.

Expected:

- Mapping created.
- Customer router mapping created or refreshed.

### RM-002 Reject interface that does not belong to router

Steps:

1. Create Router A and Router B.
2. Create interface under Router B.
3. Try mapping service to Router A with Router B interface.

Expected:

- HTTP 409.
- Mapping not created.

## 5. Radius

### RAD-001 Radius user requires router for Internet service

Steps:

1. Create internet service.
2. Try create Radius user without router_id.

Expected:

- HTTP 422 or 409.
- Error says router required.

### RAD-002 Radius NAS requires router

Steps:

1. Try create NAS without router_id.

Expected:

- HTTP 422.

### RAD-003 Radius secret is not returned after creation

Steps:

1. Create Radius user with secret.
2. Get Radius user detail.

Expected:

- Response does not include plain secret.

## 6. Suspend / Unsuspend

### SUS-001 Suspend active Internet service

Steps:

1. Prepare active Internet service with Radius user active.
2. Call suspend with reason.

Expected:

- Service status `suspended`.
- Radius user status `suspended`.
- Audit log recorded.
- Notification job queued.

### SUS-002 Unsuspend after payment

Steps:

1. Prepare suspended service.
2. Reconcile related payment.
3. Call unsuspend or trigger automatic unsuspend.

Expected:

- Service status `active`.
- Radius user status `active`.
- Audit log recorded.

### SUS-003 Reject unsuspend without reason/payment/manual approval

Steps:

1. Prepare suspended service.
2. Call unsuspend without valid reason/payment context.

Expected:

- HTTP 422 or 409.

## 7. Router Impact

### IMP-001 Router down calculates impact

Steps:

1. Create router with mapped active services and Radius users.
2. Mark router down or call impact endpoint.

Expected:

- Affected customers count > 0.
- Affected services count > 0.
- Affected Radius users count > 0.
- Revenue impact calculated.

### IMP-002 Router down can generate incident

Steps:

1. Trigger `router.down`.
2. Run impact analysis job.

Expected:

- Incident created with severity.
- Ticket can be created from incident.

## 8. API Format

### API-001 Success response follows envelope

Expected:

- `success = true`.
- `data` exists.
- `meta.request_id` exists.

### API-002 Validation error follows envelope

Expected:

- `success = false`.
- `error.code = validation_failed`.
- `error.details` contains field errors.
- `meta.request_id` exists.

## Minimal Automation Gate

Before MVP coding is considered stable:

- Tenant isolation tests pass.
- Billing create/reconcile tests pass.
- Service activation guard tests pass.
- Router mapping tests pass.
- Radius creation tests pass.
- Suspend/unsuspend tests pass.
- API response format tests pass.

