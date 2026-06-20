# PRD NEX OSS/BSS - Wireframes v3.4 Router-Centric

Peran: Product Designer dan Business Analyst

Dokumen ini merevisi Step 8 agar UI mengikuti PRD v3.4 Router-Centric.

## Gap Analysis v3.4

| Area | Gap Lama | Revisi v3.4 |
|---|---|---|
| Navigation | Belum ada menu Network router-centric | Ditambahkan Network dengan Router, Interface, Link, Capacity, Impact, SNMP |
| Router pages | Belum lengkap | Ditambahkan Router Dashboard, Detail, Capacity, Impact, Script Generator |
| Responsive | Belum mencakup halaman router | Ditambahkan desktop, tablet, mobile |
| POP/BTS | Berpotensi menjadi menu terpisah | POP/BTS hanya filter Router Role |

## Navigation

```text
Dashboard
Mitra
Customer
Catalog
Voucher
  Profile voucher
  Stok voucher
  Voucher terjual
  Voucher online
  Rekap voucher
  Template
Service
Map
  Map Pelanggan
  Map ODP
Network
  Router
  Router Interface
  Router Link
  Capacity Dashboard
  Impact Analysis
  SNMP Monitoring
  ODP
  OLT
  GenieACS
Radius
Ticket
Work Order
Inventory
GIS
Billing
Payment
Settings
  WhatsApp
  Tools
  Admin
  App Setting
```

Tidak ada menu POP atau BTS terpisah. Gunakan filter `Router Role` pada halaman Router.

Detail menu Mitra, Voucher, Map, ODP/OLT/GenieACS, dan Settings (WhatsApp/Tools/Admin/App Setting) ada di
`docs/PRD_NEX_OSS_BSS_Voucher_Mitra_NetworkOps_Addendum.md`, termasuk status build per item.

## Global Desktop Shell

```text
+--------------------------------------------------------------------------------+
| NEX OSS/BSS | Search...                                      Tenant | User      |
+--------------+-----------------------------------------------------------------+
| Dashboard    | Page Title                                      Actions          |
| Customer     |-----------------------------------------------------------------|
| Service      | Content Area                                                     |
| Billing      |                                                                 |
| Payment      |                                                                 |
| Network >    |                                                                 |
| Radius       |                                                                 |
| Ticket       |                                                                 |
| Work Order   |                                                                 |
| Settings     |                                                                 |
+--------------+-----------------------------------------------------------------+
```

## Global Tablet Shell

```text
+----------------------------------------------------------------+
| Menu | NEX OSS/BSS | Search                         | User      |
+----------------------------------------------------------------+
| Page Title                                      Actions          |
|----------------------------------------------------------------|
| Content in 2-column or stacked layout                           |
+----------------------------------------------------------------+
```

## Global Mobile Shell

```text
+--------------------------------+
| Menu | NEX OSS/BSS | User      |
+--------------------------------+
| Page Title                     |
| Primary Action                 |
|--------------------------------|
| Stacked content                |
+--------------------------------+
```

## Router Dashboard

Purpose:

- Memberi ringkasan kesehatan router, capacity, SNMP, dan impact.

Desktop:

```text
+--------------------------------------------------------------------------------+
| Router Dashboard                                      [+ Router] [Generate]      |
+--------------------------------------------------------------------------------+
| Total Router | Active | Warning | Critical | Offline | SNMP Failed              |
+--------------------------------------------------------------------------------+
| Filters: Role [All] Status [All] SNMP [All] Search [____________]               |
+--------------------------------------------------------------------------------+
| Router Table                                                                  |
| Hostname     Role        Status    SNMP          Utilization   Impact Actions  |
| bng-jkt-01   BNG         Active    Reachable     64%           0      View     |
| pop-bdg-01   POP Router  Warning   Reachable     82%           45     View     |
| bts-01       BTS Router  Offline   Unreachable   -             120    View     |
+--------------------------------------------------------------------------------+
```

Tablet:

```text
+------------------------------------------------+
| Router Dashboard                     [+]       |
| Total | Active | Warning | Offline             |
| Filters                                        |
| Router list cards                              |
| bng-jkt-01 | BNG | Active | 64% | View         |
+------------------------------------------------+
```

Mobile:

```text
+------------------------------+
| Router Dashboard          +  |
| Search                       |
| Role | Status                |
| bng-jkt-01                  |
| BNG | Active | Reachable     |
| Utilization 64% | Impact 0   |
+------------------------------+
```

## Router Detail

Purpose:

- Menampilkan identitas router, role, status, interface, NAS, service mapping, dan event timeline.

Desktop:

```text
+--------------------------------------------------------------------------------+
| Router Detail: bng-jkt-01                         [Edit] [Maintenance] [Retire] |
+--------------------------------------------------------------------------------+
| Summary: Role BNG | Status Active | SNMP Reachable | IP 10.0.0.1 | Site Jakarta |
+--------------------------------------------------------------------------------+
| Interfaces                     | Radius NAS                                      |
| ether1 Up 1G VLAN 100          | NAS IP 10.0.0.1 Vendor MikroTik Status Active    |
| ether2 Up 1G VLAN 200          | Secret: ********                                |
+--------------------------------------------------------------------------------+
| Service Mapping                                                                 |
| CID        Customer        Service        Interface        VLAN       Status     |
+--------------------------------------------------------------------------------+
| Timeline                                                                       |
+--------------------------------------------------------------------------------+
```

Mobile:

```text
+------------------------------+
| bng-jkt-01              Edit |
| BNG | Active | Reachable     |
| IP 10.0.0.1                  |
| Tabs: Summary Interfaces NAS |
| Service Mapping              |
| Timeline                     |
+------------------------------+
```

## Router Interface

```text
+--------------------------------------------------------------------------------+
| Router Interface                                      [+ Interface]             |
+--------------------------------------------------------------------------------+
| Filters: Router [All] Status [All] Type [All]                                  |
| Interface    Router       Type       IP          VLAN     Status    Actions     |
| ether1       bng-jkt-01   ethernet   10.0.0.2    100      Up        Edit        |
+--------------------------------------------------------------------------------+
```

States shown:

- Provisioning
- Up
- Down
- Disabled

## Router Link

```text
+--------------------------------------------------------------------------------+
| Router Link                                             [+ Link]                |
+--------------------------------------------------------------------------------+
| Router A      Interface A     Router B      Interface B     Capacity Status     |
| bng-jkt-01    ether1          core-jkt-01   ether2          10G      Active     |
+--------------------------------------------------------------------------------+
```

## Router Capacity

Purpose:

- Menampilkan capacity history dan warning/critical.

Desktop:

```text
+--------------------------------------------------------------------------------+
| Capacity Dashboard                                      Role [All] Site [All]   |
+--------------------------------------------------------------------------------+
| Top Utilization | CPU Warning | Memory Warning | Traffic Critical              |
+--------------------------------------------------------------------------------+
| Router Utilization Chart                                                        |
+--------------------------------------------------------------------------------+
| Router       CPU    Memory    In/Out Traffic     Utilization     Level          |
| pop-bdg-01   72%    68%       900M/850M          82%             Warning        |
+--------------------------------------------------------------------------------+
```

Mobile:

```text
+------------------------------+
| Capacity                     |
| pop-bdg-01                   |
| CPU 72% | Mem 68% | 82% Warn |
+------------------------------+
```

## Router Impact

Purpose:

- Menghitung customer/service/radius/revenue impact dari router down.

Desktop:

```text
+--------------------------------------------------------------------------------+
| Impact Analysis                                  Router [bts-01] [Run Analysis] |
+--------------------------------------------------------------------------------+
| Affected Customers | Affected Services | Radius Users | Monthly Revenue Impact  |
| 120                | 126               | 118          | IDR 25,000,000          |
+--------------------------------------------------------------------------------+
| Impacted Customers                                                              |
| Customer      CID       Service       Radius User       Status       MRR         |
+--------------------------------------------------------------------------------+
| [Create Incident] [Create Ticket] [Export]                                      |
+--------------------------------------------------------------------------------+
```

Mobile:

```text
+------------------------------+
| Impact Analysis              |
| Router: bts-01               |
| Customers 120                |
| Services 126                 |
| Radius Users 118             |
| Revenue IDR 25,000,000       |
| Create Incident              |
+------------------------------+
```

## Router Script Generator

Purpose:

- Menghasilkan script RouterOS untuk integrasi FreeRadius tanpa MikroTik API.

Desktop:

```text
+--------------------------------------------------------------------------------+
| Router Script Generator                                                        |
+--------------------------------------------------------------------------------+
| Router [bng-jkt-01] OS [ROS7] Type [PPPoE] Radius Server [10.10.10.10]          |
| Secret [********] Service Profile [100M]                         [Generate]     |
+--------------------------------------------------------------------------------+
| Generated Script                                                               |
| /radius add address=10.10.10.10 ...                                            |
|                                                                                |
| [Copy] [Download] [Audit Log]                                                   |
+--------------------------------------------------------------------------------+
```

Supported script:

- ROS6 PPPoE
- ROS7 PPPoE
- ROS6 Hotspot
- ROS7 Hotspot

Mobile:

```text
+------------------------------+
| Script Generator             |
| Router                       |
| OS Version                   |
| Script Type                  |
| Radius Server                |
| Secret                       |
| Generate                     |
| Script output                |
+------------------------------+
```

## SNMP Monitoring Dashboard

```text
+--------------------------------------------------------------------------------+
| SNMP Monitoring                                      Status [All] [Refresh]     |
+--------------------------------------------------------------------------------+
| Reachable | Unreachable | Auth Failed | Not Configured                          |
+--------------------------------------------------------------------------------+
| Router       Status          Last Check              Latency       Action       |
| bng-jkt-01   Reachable       2026-06-18 09:00        12 ms         Detail       |
| pop-bdg-01   Auth Failed     2026-06-18 09:00        -             Fix SNMP     |
+--------------------------------------------------------------------------------+
```

## Radius Screen Update

```text
+--------------------------------------------------------------------------------+
| Radius                                                                         |
+--------------------------------------------------------------------------------+
| Profiles | Users | NAS Devices                                                 |
+--------------------------------------------------------------------------------+
| Radius Users: Username | Customer | Service | Router | Profile | Status        |
| NAS Devices: Hostname | Router | NAS IP | Vendor | Status                      |
+--------------------------------------------------------------------------------+
```

## NOC Dashboard Update

```text
+--------------------------------------------------------------------------------+
| NOC Dashboard                                                                  |
+--------------------------------------------------------------------------------+
| Router Online | Router Warning | Router Critical | Customer Down | Open P1      |
+--------------------------------------------------------------------------------+
| Active Incidents                                                               |
| Incident | Router | Impact Customer | Revenue Impact | SLA | Action            |
+--------------------------------------------------------------------------------+
```

## GIS Update

Layers:

- Router
- Customer
- Fiber
- ODC
- ODP

Filters:

- Router Role
- Status
- SNMP Status
- Customer Impact

## Responsive Rules

- Desktop uses sidebar navigation and wide tables.
- Tablet uses collapsible navigation and two-column panels.
- Mobile uses stacked cards, sticky primary action, and tabs for dense detail.
- Router tables collapse into summary cards on mobile.
- Impact Analysis keeps metrics visible before detailed list on all viewports.

## QC Checklist

- Menu Network tersedia dengan Router, Router Interface, Router Link, Capacity Dashboard, Impact Analysis, SNMP Monitoring.
- Halaman tersedia: Router Dashboard, Router Detail, Router Capacity, Router Impact, Router Script Generator, SNMP Monitoring Dashboard.
- Desktop, tablet, dan mobile tersedia.
- Tidak ada menu POP/BTS terpisah.
