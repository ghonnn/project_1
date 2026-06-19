# NEX OSS/BSS v3.4 - API Standards

Dokumen ini mengunci standar response API, error format, pagination, tenant isolation, dan audit log.

## Base URL

```text
/api/v1
```

## Authentication

Use Bearer token:

```http
Authorization: Bearer <access_token>
```

Token must include:

- `user_id`
- `tenant_id` for tenant users
- `roles`
- `permissions`
- `is_platform_owner`

## Tenant Context

Tenant-scoped endpoint uses:

```text
/tenants/{tenant_id}/...
```

Rules:

- If user is tenant-scoped, `{tenant_id}` must equal token tenant.
- Platform Owner may access any tenant.
- Query must always include tenant filter for tenant-scoped data.
- Cross-tenant relation is forbidden even if UUID exists.

## Success Response

Single resource:

```json
{
  "success": true,
  "data": {
    "id": "uuid"
  },
  "meta": {
    "request_id": "req_abc123",
    "timestamp": "2026-06-19T02:00:00Z"
  }
}
```

Collection:

```json
{
  "success": true,
  "data": [],
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 120,
    "total_pages": 6
  },
  "meta": {
    "request_id": "req_abc123",
    "timestamp": "2026-06-19T02:00:00Z"
  }
}
```

Delete:

```http
204 No Content
```

## Error Response

```json
{
  "success": false,
  "error": {
    "code": "validation_failed",
    "message": "Validation failed.",
    "details": {
      "router_id": ["Router is required for Internet service."]
    }
  },
  "meta": {
    "request_id": "req_abc123",
    "timestamp": "2026-06-19T02:00:00Z"
  }
}
```

## Error Codes

| HTTP | Code | Meaning |
|---|---|---|
| 400 | `bad_request` | Invalid payload shape |
| 401 | `unauthorized` | Missing/invalid token |
| 403 | `forbidden` | Permission denied |
| 404 | `not_found` | Entity not found or hidden by tenant isolation |
| 409 | `business_rule_conflict` | Business rule violation |
| 422 | `validation_failed` | Field validation failed |
| 429 | `rate_limited` | Too many requests |
| 500 | `server_error` | Unexpected server error |

## Pagination

Query:

```text
?page=1&limit=20
```

Rules:

- Default `page = 1`.
- Default `limit = 20`.
- Maximum `limit = 100`.
- Use stable sort. Default: `created_at desc`.

## Filtering and Sorting

Use explicit query params:

```text
GET /routers?role=bng&status=active&snmp_status=reachable&q=jkt
```

Sorting:

```text
?sort=-created_at
?sort=hostname
```

Only allow whitelisted sort fields per endpoint.

## Validation Rules

Global:

- UUID fields must be valid UUID.
- Money fields must be numeric >= 0.
- Secret fields must never be returned after creation.
- All path tenant IDs must pass tenant context check.

Router-centric:

- Service Internet requires router mapping before activation.
- Radius user for network service requires router ID.
- NAS device requires router ID.
- Router deactivation requires no active primary service mapping unless override approved.

## Audit Log

Audit all sensitive actions:

- Login failure.
- Role/permission changes.
- Customer deactivate.
- Service activate/suspend/unsuspend/terminate.
- Router create/update/deactivate.
- Router interface/link update.
- Radius profile/user/NAS create/update.
- Router script generation.
- Invoice create/adjust/write-off.
- Payment reconcile/refund.

Audit fields:

```json
{
  "tenant_id": "uuid",
  "actor_user_id": "uuid",
  "action": "router.updated",
  "target_table": "routers",
  "target_id": "uuid",
  "request_id": "req_abc123",
  "ip_address": "127.0.0.1",
  "user_agent": "string",
  "payload": {
    "before": {},
    "after": {}
  }
}
```

Do not log:

- Plain password.
- Radius secret.
- NAS secret.
- Payment credential.

## Idempotency

Use `Idempotency-Key` header for:

- Payment webhook.
- Payment reconciliation.
- Invoice generation.
- Service activation.
- Suspend/unsuspend.

Duplicate key returns the previous result if payload hash matches.

## Request ID

Every request must have request ID:

- Accept `X-Request-ID` from client if valid.
- Generate one if missing.
- Include in response meta and logs.

