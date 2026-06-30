@php
    $domId = 'nex-snmp-'.str_replace('-', '', $router->id);
@endphp

<div
    id="{{ $domId }}"
    class="nex-snmp-dashboard"
    x-data="{
        endpoint: @js($endpoint),
        timer: null,
        activeTab: 'dashboard',
        statusText: 'Loading...',
        statusState: '',
        data: {
            cpu_load: 0,
            memory_used_percent: 0,
            disk_used_percent: 0,
            interfaces: [],
            pppoe_sessions: [],
            hotspot_sessions: [],
            identity: '-',
            model: '-',
            uptime: '-',
            version: '-',
            license: '-',
            temperature: '-',
            cpu_detail: '-',
            memory_detail: '-',
            disk_detail: '-',
        },
        async fetchSnapshot() {
            try {
                const response = await fetch(this.endpoint, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
                if (! response.ok) throw new Error(`HTTP ${response.status}`);
                const payload = await response.json();
                this.data = { ...this.data, ...payload };
                this.statusState = payload.status || 'unreachable';
                this.statusText = `${payload.status === 'reachable' ? 'SNMP Online' : 'SNMP Offline'} / ${payload.updated_at || '-'}`;
            } catch (error) {
                this.statusState = 'unreachable';
                this.statusText = 'SNMP Offline';
            }
        },
        start() {
            this.fetchSnapshot();
            this.timer = setInterval(() => this.fetchSnapshot(), 1000);
        },
        stop() {
            if (this.timer) clearInterval(this.timer);
        },
    }"
    x-init="start()"
    x-on:remove.window="stop()"
>
    <style>
        #{{ $domId }} {
            margin: -12px;
            padding: 26px;
            border-radius: 10px;
            background: #111827;
            color: #f8fafc;
            font-family: "Segoe UI", Inter, ui-sans-serif, system-ui, sans-serif;
        }

        #{{ $domId }} .snmp-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 24px;
        }

        #{{ $domId }} .snmp-brand {
            display: flex;
            align-items: center;
            gap: 22px;
        }

        #{{ $domId }} .snmp-brand h2 {
            margin: 0;
            color: #ffffff;
            font-size: 26px;
            font-weight: 800;
            letter-spacing: 0;
        }

        #{{ $domId }} .snmp-tabs {
            display: flex;
            gap: 18px;
            color: #94a3b8;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
        }

        #{{ $domId }} .snmp-tab {
            border: 0;
            background: transparent;
            color: #94a3b8;
            padding: 0;
            font: inherit;
            cursor: pointer;
        }

        #{{ $domId }} .snmp-tab:hover,
        #{{ $domId }} .snmp-tab.is-active {
            color: #ffffff;
        }

        #{{ $domId }} .snmp-reboot {
            min-height: 36px;
            border-radius: 6px;
            border: 0;
            background: #ef4444;
            color: #ffffff;
            padding: 0 14px;
            font-size: 13px;
            font-weight: 800;
        }

        #{{ $domId }} .snmp-panel {
            border: 1px solid rgba(148, 163, 184, .12);
            border-radius: 6px;
            background: #1f2a44;
            padding: 28px 20px 26px;
        }

        #{{ $domId }} .snmp-gauges {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        #{{ $domId }} .snmp-gauge-card {
            display: grid;
            justify-items: center;
            gap: 12px;
            min-height: 190px;
            border-radius: 6px;
            background: rgba(15, 23, 42, .1);
        }

        #{{ $domId }} .snmp-gauge {
            position: relative;
            width: 210px;
            height: 112px;
            overflow: hidden;
        }

        #{{ $domId }} .snmp-gauge::before {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: 210px 210px 0 0;
            background: conic-gradient(from 270deg at 50% 100%, #38bdf8 0deg, #38bdf8 calc(var(--value, 0) * 1.8deg), #f8fafc calc(var(--value, 0) * 1.8deg), #f8fafc 180deg, transparent 180deg);
        }

        #{{ $domId }} .snmp-gauge::after {
            content: "";
            position: absolute;
            left: 45px;
            right: 45px;
            bottom: -1px;
            height: 66px;
            border-radius: 100px 100px 0 0;
            background: #1f2a44;
        }

        #{{ $domId }} .snmp-gauge-value {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 12px;
            z-index: 1;
            text-align: center;
            color: #ffffff;
            font-size: 28px;
            font-weight: 900;
        }

        #{{ $domId }} .snmp-gauge-label {
            color: #cbd5e1;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
        }

        #{{ $domId }} .snmp-info-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px 24px;
        }

        #{{ $domId }} .snmp-info-card {
            min-height: 72px;
            border: 1px solid rgba(148, 163, 184, .18);
            border-radius: 5px;
            padding: 14px 20px;
            background: rgba(30, 41, 59, .4);
        }

        #{{ $domId }} .snmp-info-value {
            color: #e0e7ff;
            font-size: 20px;
            font-weight: 800;
            word-break: break-word;
        }

        #{{ $domId }} .snmp-info-label {
            margin-top: 6px;
            color: #ffffff;
            font-size: 12px;
            font-weight: 700;
        }

        #{{ $domId }} .snmp-status {
            color: #94a3b8;
            font-size: 13px;
            font-weight: 700;
        }

        #{{ $domId }} .snmp-status[data-state="reachable"] {
            color: #34d399;
        }

        #{{ $domId }} .snmp-status[data-state="unreachable"] {
            color: #f87171;
        }

        #{{ $domId }} .snmp-section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
            color: #ffffff;
            font-size: 15px;
            font-weight: 800;
        }

        #{{ $domId }} .snmp-section-count {
            color: #94a3b8;
            font-size: 13px;
            font-weight: 700;
        }

        #{{ $domId }} .snmp-table-wrap {
            overflow-x: auto;
            border: 1px solid rgba(148, 163, 184, .2);
            border-radius: 6px;
        }

        #{{ $domId }} .snmp-table {
            width: 100%;
            min-width: 820px;
            border-collapse: collapse;
            color: #e5e7eb;
            font-size: 14px;
        }

        #{{ $domId }} .snmp-table th,
        #{{ $domId }} .snmp-table td {
            border-bottom: 1px solid rgba(148, 163, 184, .16);
            padding: 12px 14px;
            text-align: left;
            white-space: nowrap;
        }

        #{{ $domId }} .snmp-table th {
            background: rgba(15, 23, 42, .35);
            color: #cbd5e1;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
        }

        #{{ $domId }} .snmp-table tr:last-child td {
            border-bottom: 0;
        }

        #{{ $domId }} .snmp-empty {
            border: 1px dashed rgba(148, 163, 184, .28);
            border-radius: 6px;
            padding: 22px;
            color: #cbd5e1;
            font-size: 14px;
            text-align: center;
        }

        #{{ $domId }} .snmp-badge {
            display: inline-flex;
            align-items: center;
            min-height: 24px;
            border-radius: 999px;
            background: rgba(16, 185, 129, .16);
            color: #6ee7b7;
            padding: 0 10px;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
        }

        @media (max-width: 980px) {
            #{{ $domId }} .snmp-gauges,
            #{{ $domId }} .snmp-info-grid {
                grid-template-columns: 1fr;
            }

            #{{ $domId }} .snmp-topbar,
            #{{ $domId }} .snmp-brand {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>

    <div class="snmp-topbar">
        <div class="snmp-brand">
            <h2>MikroTik</h2>
            <div class="snmp-tabs">
                <button type="button" class="snmp-tab" x-bind:class="{ 'is-active': activeTab === 'dashboard' }" x-on:click="activeTab = 'dashboard'">Dashboard</button>
                <button type="button" class="snmp-tab" x-bind:class="{ 'is-active': activeTab === 'interface' }" x-on:click="activeTab = 'interface'">Interface</button>
                <button type="button" class="snmp-tab" x-bind:class="{ 'is-active': activeTab === 'pppoe' }" x-on:click="activeTab = 'pppoe'">PPPoE</button>
                <button type="button" class="snmp-tab" x-bind:class="{ 'is-active': activeTab === 'hotspot' }" x-on:click="activeTab = 'hotspot'">Hotspot</button>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="snmp-status" x-bind:data-state="statusState" x-text="statusText">Loading...</span>
            <button type="button" class="snmp-reboot" x-on:click="fetchSnapshot()">REFRESH</button>
        </div>
    </div>

    <section class="snmp-panel" x-show="activeTab === 'dashboard'">
        <div class="snmp-gauges">
            <div class="snmp-gauge-card">
                <div class="snmp-gauge" x-bind:style="`--value: ${Math.max(0, Math.min(100, Number(data.cpu_load || 0)))}`"><div class="snmp-gauge-value" x-text="`${Math.round(Number(data.cpu_load || 0))}%`">0%</div></div>
                <div class="snmp-gauge-label">CPU Load</div>
            </div>
            <div class="snmp-gauge-card">
                <div class="snmp-gauge" x-bind:style="`--value: ${Math.max(0, Math.min(100, Number(data.memory_used_percent || 0)))}`"><div class="snmp-gauge-value" x-text="`${Math.round(Number(data.memory_used_percent || 0))}%`">0%</div></div>
                <div class="snmp-gauge-label">Memory Terpakai</div>
            </div>
            <div class="snmp-gauge-card">
                <div class="snmp-gauge" x-bind:style="`--value: ${Math.max(0, Math.min(100, Number(data.disk_used_percent || 0)))}`"><div class="snmp-gauge-value" x-text="`${Math.round(Number(data.disk_used_percent || 0))}%`">0%</div></div>
                <div class="snmp-gauge-label">Disk Terpakai</div>
            </div>
        </div>

        <div class="snmp-info-grid">
            <div class="snmp-info-card"><div class="snmp-info-value" x-text="data.identity">-</div><div class="snmp-info-label">MikroTik Identity</div></div>
            <div class="snmp-info-card"><div class="snmp-info-value" x-text="data.model">-</div><div class="snmp-info-label">MikroTik Model</div></div>
            <div class="snmp-info-card"><div class="snmp-info-value" x-text="data.uptime">-</div><div class="snmp-info-label">System uptime</div></div>
            <div class="snmp-info-card"><div class="snmp-info-value" x-text="data.version">-</div><div class="snmp-info-label">ROS version</div></div>
            <div class="snmp-info-card"><div class="snmp-info-value" x-text="data.license">-</div><div class="snmp-info-label">MikroTik License</div></div>
            <div class="snmp-info-card"><div class="snmp-info-value" x-text="data.temperature">-</div><div class="snmp-info-label">Temperature/Voltage</div></div>
            <div class="snmp-info-card"><div class="snmp-info-value" x-text="data.cpu_detail">-</div><div class="snmp-info-label">CPU Count / Load</div></div>
            <div class="snmp-info-card"><div class="snmp-info-value" x-text="data.memory_detail">-</div><div class="snmp-info-label">Memory terpakai/total</div></div>
            <div class="snmp-info-card"><div class="snmp-info-value" x-text="data.disk_detail">-</div><div class="snmp-info-label">Disk terpakai/total</div></div>
        </div>
    </section>

    <section class="snmp-panel" x-show="activeTab === 'interface'" x-cloak>
        <div class="snmp-section-head">
            <span>Interface Router</span>
            <span class="snmp-section-count" x-text="`${data.interfaces?.length || 0} interface`">0 interface</span>
        </div>
        <template x-if="data.interfaces?.length">
            <div class="snmp-table-wrap">
                <table class="snmp-table">
                    <thead>
                        <tr>
                            <th>Interface</th>
                            <th>Type</th>
                            <th>IP Address</th>
                            <th>VLAN</th>
                            <th>Speed</th>
                            <th>MTU</th>
                            <th>Admin</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="item in data.interfaces" x-bind:key="`${item.name}-${item.vlan}`">
                            <tr>
                                <td x-text="item.name || '-'">-</td>
                                <td x-text="item.type || '-'">-</td>
                                <td x-text="item.ip_address || '-'">-</td>
                                <td x-text="item.vlan || '-'">-</td>
                                <td x-text="item.speed || '-'">-</td>
                                <td x-text="item.mtu || '-'">-</td>
                                <td><span class="snmp-badge" x-text="item.admin_status || '-'">-</span></td>
                                <td><span class="snmp-badge" x-text="item.status || '-'">-</span></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </template>
        <div class="snmp-empty" x-show="!data.interfaces?.length">Belum ada data interface router.</div>
    </section>

    <section class="snmp-panel" x-show="activeTab === 'pppoe'" x-cloak>
        <div class="snmp-section-head">
            <span>PPPoE Online</span>
            <span class="snmp-section-count" x-text="`${data.pppoe_sessions?.length || 0} sesi aktif`">0 sesi aktif</span>
        </div>
        <template x-if="data.pppoe_sessions?.length">
            <div class="snmp-table-wrap">
                <table class="snmp-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Pelanggan</th>
                            <th>CID</th>
                            <th>Profile</th>
                            <th>IP Address</th>
                            <th>NAS Port</th>
                            <th>Uptime</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="item in data.pppoe_sessions" x-bind:key="`${item.username}-${item.nas_port}`">
                            <tr>
                                <td x-text="item.username || '-'">-</td>
                                <td x-text="item.customer || '-'">-</td>
                                <td x-text="item.cid || '-'">-</td>
                                <td x-text="item.profile || '-'">-</td>
                                <td x-text="item.ip_address || '-'">-</td>
                                <td x-text="item.nas_port || '-'">-</td>
                                <td x-text="item.uptime || '-'">-</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </template>
        <div class="snmp-empty" x-show="!data.pppoe_sessions?.length">Belum ada sesi PPPoE aktif dari radacct.</div>
    </section>

    <section class="snmp-panel" x-show="activeTab === 'hotspot'" x-cloak>
        <div class="snmp-section-head">
            <span>Hotspot Online</span>
            <span class="snmp-section-count" x-text="`${data.hotspot_sessions?.length || 0} sesi aktif`">0 sesi aktif</span>
        </div>
        <template x-if="data.hotspot_sessions?.length">
            <div class="snmp-table-wrap">
                <table class="snmp-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Pelanggan</th>
                            <th>CID</th>
                            <th>Profile</th>
                            <th>IP Address</th>
                            <th>NAS Port</th>
                            <th>Uptime</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="item in data.hotspot_sessions" x-bind:key="`${item.username}-${item.nas_port}`">
                            <tr>
                                <td x-text="item.username || '-'">-</td>
                                <td x-text="item.customer || '-'">-</td>
                                <td x-text="item.cid || '-'">-</td>
                                <td x-text="item.profile || '-'">-</td>
                                <td x-text="item.ip_address || '-'">-</td>
                                <td x-text="item.nas_port || '-'">-</td>
                                <td x-text="item.uptime || '-'">-</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </template>
        <div class="snmp-empty" x-show="!data.hotspot_sessions?.length">Belum ada sesi Hotspot aktif dari radacct.</div>
    </section>
</div>
