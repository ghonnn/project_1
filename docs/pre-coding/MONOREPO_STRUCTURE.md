# NEX OSS/BSS v3.4 - Monorepo Structure

Dokumen ini mendefinisikan struktur repository sebelum source code dibuat.

## Recommended Structure

```text
nexbil/
├── apps/
│   ├── backend/                 # Laravel API
│   ├── web-admin/                # Admin/NOC/Finance web app
│   ├── customer-portal/          # Customer portal web app
│   └── mobile/                   # Flutter app, phase lanjut
├── packages/
│   ├── api-client/               # Generated client from OpenAPI
│   ├── shared-types/             # Shared TS types if frontend uses TS
│   └── ui/                       # Shared UI components
├── api/
│   └── openapi.yaml
├── database/
│   ├── migrations/
│   │   └── 0001_initial_router_centric_schema.sql
│   └── seeds/
│       └── 0001_core_seed.sql
├── docs/
│   ├── pre-coding/
│   └── PRD_*.md
├── infra/
│   ├── docker/
│   ├── compose/
│   └── k8s/
├── scripts/
│   └── export_prd_latest.py
├── exports/
├── .github/
│   └── workflows/
├── .gitignore
└── README.md
```

## Initial Coding Order

1. Create `apps/backend` Laravel project.
2. Wire PostgreSQL/Redis via Docker Compose.
3. Port SQL schema into Laravel migrations.
4. Port SQL seed into Laravel seeders.
5. Implement auth, tenant, RBAC.
6. Implement customer/product/service.
7. Implement router/radius/billing flow.

## Ownership Rules

- `api/openapi.yaml` is the API source of truth.
- `database/migrations/*.sql` is the pre-coding database source of truth.
- Laravel migrations may be generated from SQL, but business constraints must remain equivalent.
- `docs/pre-coding` contains engineering rules that should be reviewed before sprint start.
- Generated exports should not be edited by hand.

## Laravel Backend Structure

```text
apps/backend/
├── app/
│   ├── Domains/
│   │   ├── Auth/
│   │   ├── Tenant/
│   │   ├── Rbac/
│   │   ├── Customer/
│   │   ├── Product/
│   │   ├── Service/
│   │   ├── Billing/
│   │   ├── Payment/
│   │   ├── Network/
│   │   ├── Radius/
│   │   ├── Ticket/
│   │   ├── WorkOrder/
│   │   ├── Monitoring/
│   │   └── Audit/
│   ├── Http/
│   │   ├── Controllers/Api/V1/
│   │   ├── Middleware/
│   │   ├── Requests/
│   │   └── Resources/
│   └── Support/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── routes/
│   └── api.php
└── tests/
    ├── Feature/
    └── Unit/
```

## Branching Recommendation

- `main`: stable.
- `develop`: integration.
- `feature/phase-1-auth-rbac`.
- `feature/phase-1-customer-service`.
- `feature/phase-4-router-radius`.

## Do Not Create

- Separate POP app/module.
- Separate BTS app/module.
- Tables named `pop`, `pop_sites`, `bts`, `bts_sites`.

POP and BTS must stay as `router_role` values.

