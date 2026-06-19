# PRD NEX OSS/BSS - State Machine Diagrams v3.4 Router-Centric

Peran: Enterprise Solution Architect

Dokumen ini merevisi Step 7 agar state machine konsisten dengan PRD v3.4 Router-Centric.

## Gap Analysis v3.4

| Area | Gap Lama | Revisi v3.4 |
|---|---|---|
| Router | Belum ada state machine Router | Ditambahkan Router: Draft sampai Retired |
| Router Interface | Belum ada state interface | Ditambahkan Provisioning, Up, Down, Disabled |
| Radius User | State lama tidak sama dengan PRD v3.4 | Diganti menjadi Pending, Active, Suspended, Terminated |
| Service | Belum mewajibkan router mapping untuk Internet | Ditambahkan validation router mapping |

## Customer

States:

- `prospect`
- `lead`
- `qualified`
- `contracted`
- `active`
- `suspended`
- `terminated`

Transitions:

- `prospect -> lead`
- `lead -> qualified`
- `qualified -> contracted`
- `contracted -> active`
- `active -> suspended`
- `suspended -> active`
- `active -> terminated`
- `suspended -> terminated`

Validation:

- `contracted` requires signed contract.
- `active` requires at least one active service.
- `terminated` requires service cleanup and final billing handling.

## Service

States:

- `requested`
- `provisioning`
- `active`
- `suspended`
- `terminated`
- `failed`

Transitions:

- `requested -> provisioning`
- `provisioning -> active`
- `provisioning -> failed`
- `active -> suspended`
- `suspended -> active`
- `active -> terminated`
- `suspended -> terminated`
- `failed -> terminated`

Validation:

- `active` requires contract and valid product/service category.
- Internet/network service requires `service_router_mapping`.
- Service with `requires_radius = true` requires active radius user.
- `suspended` requires reason and audit record.

## Router

States:

- `Draft`
- `Provisioning`
- `Active`
- `Warning`
- `Critical`
- `Maintenance`
- `Offline`
- `Retired`

Transitions:

- `Draft -> Provisioning`
- `Provisioning -> Active`
- `Provisioning -> Offline`
- `Active -> Warning`
- `Warning -> Active`
- `Warning -> Critical`
- `Critical -> Warning`
- `Critical -> Offline`
- `Active -> Maintenance`
- `Warning -> Maintenance`
- `Critical -> Maintenance`
- `Maintenance -> Active`
- `Active -> Offline`
- `Offline -> Active`
- `Offline -> Retired`
- `Maintenance -> Retired`

Triggers:

- `Draft -> Provisioning`: router record completed.
- `Provisioning -> Active`: interface/SNMP/NAS setup completed.
- `Active -> Warning`: capacity warning or degraded SNMP.
- `Warning -> Critical`: capacity critical or severe packet loss.
- `Critical -> Offline`: router down.
- `Active -> Offline`: SNMP unreachable or manual outage.
- `Offline -> Active`: router recovered.
- `Any non-retired -> Maintenance`: planned maintenance.
- `Offline/Maintenance -> Retired`: decommission approved.

Validation:

- `Provisioning` requires hostname, router role, management IP, and tenant.
- `Active` requires at least one interface or explicit exception.
- `Warning/Critical` requires metric or monitoring evidence.
- `Retired` requires no primary active service mapping.

## Router Interface

States:

- `Provisioning`
- `Up`
- `Down`
- `Disabled`

Transitions:

- `Provisioning -> Up`
- `Provisioning -> Down`
- `Up -> Down`
- `Down -> Up`
- `Up -> Disabled`
- `Down -> Disabled`
- `Disabled -> Provisioning`

Triggers:

- Interface created
- SNMP interface status update
- Admin disable/enable
- Repair completed

Validation:

- `Up` requires parent router not retired.
- `Disabled` requires admin reason.
- Interface used by active service cannot be disabled without override and impact confirmation.

## Radius User

States:

- `Pending`
- `Active`
- `Suspended`
- `Terminated`

Transitions:

- `Pending -> Active`
- `Active -> Suspended`
- `Suspended -> Active`
- `Active -> Terminated`
- `Suspended -> Terminated`
- `Pending -> Terminated`

Triggers:

- `Pending -> Active`: FreeRadius sync and auth test successful.
- `Active -> Suspended`: service suspended or billing overdue.
- `Suspended -> Active`: payment reconciled or manual unsuspend.
- `Active/Suspended -> Terminated`: service termination.

Validation:

- `Active` requires customer, service, profile, and router when network service requires mapping.
- `Suspended` requires linked service suspend reason.
- `Terminated` requires service termination or cleanup approval.

## Invoice

States:

- `draft`
- `issued`
- `overdue`
- `partial_paid`
- `paid`
- `written_off`
- `cancelled`

Transitions:

- `draft -> issued`
- `issued -> overdue`
- `issued -> partial_paid`
- `partial_paid -> paid`
- `issued -> paid`
- `overdue -> paid`
- `overdue -> written_off`
- `draft -> cancelled`
- `issued -> cancelled`

Validation:

- `issued` requires invoice number, due date, and items.
- `paid` requires paid amount equals total amount.
- `written_off` requires approval and audit.

## Payment

States:

- `initiated`
- `authorized`
- `captured`
- `reconciled`
- `failed`
- `refunded`
- `cancelled`

Transitions:

- `initiated -> authorized`
- `authorized -> captured`
- `captured -> reconciled`
- `initiated -> failed`
- `captured -> failed`
- `reconciled -> refunded`
- `authorized -> cancelled`

## Ticket

States:

- `new`
- `acknowledged`
- `assigned`
- `in_progress`
- `resolved`
- `awaiting_customer`
- `closed`
- `escalated`

Validation:

- Ticket from router incident must include `router_id` or `incident_id`.
- `resolved` requires resolution note.

## Work Order

States:

- `created`
- `planned`
- `scheduled`
- `executing`
- `completed`
- `verified`
- `closed`
- `cancelled`

Validation:

- Network work order can reference router, interface, or router link.
- Completion requires report, photo/GPS evidence where field work applies.

## Contract

States:

- `draft`
- `negotiation`
- `signed`
- `active`
- `renewal_pending`
- `expired`
- `terminated`

Validation:

- Service activation requires signed or active contract unless explicitly waived by tenant policy.

## Summary

```text
Router: Draft -> Provisioning -> Active -> Warning/Critical -> Offline -> Active -> Retired
Router Interface: Provisioning -> Up -> Down -> Up / Disabled
Radius User: Pending -> Active -> Suspended -> Active / Terminated
Service: requested -> provisioning -> active -> suspended -> active / terminated
```

## QC Checklist

- Router state tersedia: Draft, Provisioning, Active, Warning, Critical, Maintenance, Offline, Retired.
- Router Interface state tersedia: Provisioning, Up, Down, Disabled.
- Radius User state tersedia: Pending, Active, Suspended, Terminated.
- Service Internet tidak bisa aktif tanpa router mapping.
