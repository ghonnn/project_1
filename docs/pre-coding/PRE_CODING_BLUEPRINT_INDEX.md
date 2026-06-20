# NEX OSS/BSS v3.4 - Pre-Coding Blueprint Index

Dokumen ini adalah index paket pre-coding agar development berjalan rapi.

## File Teknis

- `api/openapi.yaml`: kontrak API final per endpoint.
- `database/migrations/0001_initial_router_centric_schema.sql`: migration SQL awal PostgreSQL.
- `database/seeds/0001_core_seed.sql`: seed tenant, role, permission, service category, product, Radius profile, router template.

## Dokumen Pre-Coding

- `docs/PRD_NEX_OSS_BSS_Voucher_Mitra_NetworkOps_Addendum.md`: addendum menu Mitra, Voucher, Map/ODP/OLT/GenieACS, Tiket, dan Pengaturan (WhatsApp/Tools/Admin/Setting), termasuk status build dan gap.
- `docs/pre-coding/SPRINT_TASK_BREAKDOWN.md`: breakdown task sprint per phase.
- `docs/pre-coding/BACKEND_STACK_LARAVEL.md`: keputusan stack backend Laravel.
- `docs/pre-coding/MONOREPO_STRUCTURE.md`: struktur repo monorepo.
- `docs/pre-coding/API_STANDARDS.md`: standar response, error, pagination, audit log.
- `docs/pre-coding/TEST_SCENARIOS_MVP.md`: test scenario minimal.

## Coding Gate

Sebelum mulai coding, review dan approve:

1. OpenAPI endpoint dan schema.
2. Migration SQL dan constraint tenant/router/radius.
3. Seed role/permission.
4. Sprint task Phase 0-4.
5. Stack Laravel dan struktur monorepo.
6. API standard.
7. Test scenario minimal.

## Golden Rules

- Router adalah node utama topologi.
- POP/BTS hanya Router Role.
- Customer tidak langsung ke POP/BTS.
- Service Internet wajib Router Mapping.
- Radius user untuk service jaringan wajib terkait Router.
- Suspend/unsuspend wajib sinkron dengan Radius.
- Tenant isolation wajib di middleware, query, policy, dan test.

