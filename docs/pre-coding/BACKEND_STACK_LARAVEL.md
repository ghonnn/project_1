# NEX OSS/BSS v3.4 - Final Backend Stack with Laravel

Dokumen ini mengunci stack backend sebelum coding dimulai.

## Final Decision

Backend utama menggunakan Laravel API monolith modular terlebih dahulu.

Alasan:

- Lebih cepat untuk MVP OSS/BSS yang domainnya luas.
- Ekosistem auth, queue, validation, migration, testing, dan admin tooling matang.
- Tim bisa memecah domain menjadi module tanpa memaksa microservices terlalu awal.
- Event, queue, dan adapter tetap disiapkan agar nanti service tertentu dapat dipisah.

## Runtime

| Layer | Pilihan |
|---|---|
| Language | PHP 8.3+ |
| Framework | Laravel 11/12 API |
| Database | PostgreSQL 15+ |
| Cache | Redis |
| Queue | Redis Queue untuk MVP, RabbitMQ jika event volume naik |
| Storage | S3-compatible storage |
| Auth | Laravel Sanctum untuk MVP API token, siap migrasi OAuth2/OIDC |
| Testing | Pest atau PHPUnit |
| Static Analysis | Larastan/PHPStan |
| Formatter | Laravel Pint |
| API Docs | OpenAPI YAML sebagai source of truth |
| Container | Docker Compose untuk local/dev |

## Laravel Packages Recommended

- `laravel/sanctum` untuk token API.
- `spatie/laravel-permission` untuk RBAC, atau custom RBAC jika butuh tenant-scoped permission ketat.
- `spatie/laravel-query-builder` untuk filtering/sorting standar.
- `spatie/laravel-activitylog` boleh dipakai sebagai referensi, tetapi audit log OSS/BSS sebaiknya custom agar payload, request_id, tenant_id, dan target entity konsisten.
- `laravel/horizon` jika memakai Redis queue.
- `pestphp/pest` untuk test.
- `nunomaduro/larastan` untuk static analysis.

## Modular Domain Structure

Laravel app dibagi per domain:

- Auth
- Tenant
- RBAC
- Customer
- Product
- Service
- Billing
- Payment
- Network
- Radius
- Ticket
- WorkOrder
- Monitoring
- Audit
- Reporting

Gunakan service layer dan action classes untuk logic bisnis yang sensitif:

- `ActivateServiceAction`
- `SuspendServiceAction`
- `UnsuspendServiceAction`
- `MapServiceToRouterAction`
- `CalculateRouterImpactAction`
- `GenerateRouterScriptAction`
- `ReconcilePaymentAction`

## Key Backend Rules

- Semua table tenant-scoped wajib memiliki `tenant_id`.
- Semua query tenant-scoped wajib memakai tenant context.
- Jangan mengandalkan frontend untuk tenant isolation.
- Service Internet tidak boleh aktif tanpa router mapping.
- Radius user untuk layanan jaringan wajib terkait service dan router.
- Suspend/unsuspend harus atomic terhadap service dan Radius user.
- Semua aksi sensitif wajib audit log.

## API Layer Rules

- Controller tipis.
- Request validation via Form Request.
- Response via API Resource.
- Domain logic via Action/Service.
- Query list via Query Builder standard.
- Error format mengikuti `docs/pre-coding/API_STANDARDS.md`.

## Transaction Boundary

Gunakan DB transaction untuk:

- Service activation.
- Router mapping.
- Radius user creation.
- Invoice generation.
- Payment reconciliation.
- Suspend/unsuspend.
- Incident generation from router impact.

## Event and Queue

Event Laravel internal:

- `ServiceActivated`
- `ServiceSuspended`
- `ServiceUnsuspended`
- `RouterDown`
- `RouterCapacityWarning`
- `RouterCapacityCritical`
- `RadiusUserCreated`
- `PaymentReconciled`

Queue jobs:

- Sync FreeRadius.
- Send notification.
- Calculate impact.
- Generate report.
- Poll monitoring integration.

## Security Baseline

- Hash password with Argon2id or bcrypt.
- Encrypt NAS secret and Radius secret.
- Never log secret values.
- Use request_id in every log and API response.
- Rate-limit auth endpoints.
- Audit privileged actions.
- Apply tenant middleware before controller.

## Local Development Services

Minimum Docker Compose:

- `backend` Laravel PHP-FPM/Octane optional.
- `nginx` or Caddy.
- `postgres`.
- `redis`.
- `mailpit`.
- Optional `minio`.

## Done Before Coding

- OpenAPI reviewed.
- Migration reviewed.
- Seed reviewed.
- API standard approved.
- Test scenarios approved.
- Monorepo structure approved.

