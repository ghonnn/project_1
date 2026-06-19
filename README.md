# NEX OSS/BSS ISP Cloud Platform

Router-centric OSS/BSS MVP monorepo.

Topology rule:

```text
Customer -> Service -> Router -> Router Interface -> Radius NAS -> FreeRadius
```

POP and BTS are not modules or tables. They are only `router_role` values on `routers`.

## Apps

- `apps/api-laravel`: Laravel backend API MVP.

## Local Quick Start

See [README_LOCAL.md](README_LOCAL.md).
