@php
    $domId = 'nex-snmp-'.str_replace('-', '', $router->id);
@endphp

<div id="{{ $domId }}" class="nex-snmp-dashboard" data-endpoint="{{ $endpoint }}">
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

        #{{ $domId }} .snmp-tabs span:first-child {
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
                <span>Dashboard</span>
                <span>Interface</span>
                <span>PPPoE</span>
                <span>Hotspot</span>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="snmp-status" data-snmp-status>Loading...</span>
            <button type="button" class="snmp-reboot" data-snmp-refresh>REFRESH</button>
        </div>
    </div>

    <section class="snmp-panel">
        <div class="snmp-gauges">
            <div class="snmp-gauge-card">
                <div class="snmp-gauge" data-gauge="cpu_load" style="--value: 0"><div class="snmp-gauge-value">0%</div></div>
                <div class="snmp-gauge-label">CPU Load</div>
            </div>
            <div class="snmp-gauge-card">
                <div class="snmp-gauge" data-gauge="memory_used_percent" style="--value: 0"><div class="snmp-gauge-value">0%</div></div>
                <div class="snmp-gauge-label">Memory Terpakai</div>
            </div>
            <div class="snmp-gauge-card">
                <div class="snmp-gauge" data-gauge="disk_used_percent" style="--value: 0"><div class="snmp-gauge-value">0%</div></div>
                <div class="snmp-gauge-label">Disk Terpakai</div>
            </div>
        </div>

        <div class="snmp-info-grid">
            <div class="snmp-info-card"><div class="snmp-info-value" data-field="identity">-</div><div class="snmp-info-label">MikroTik Identity</div></div>
            <div class="snmp-info-card"><div class="snmp-info-value" data-field="model">-</div><div class="snmp-info-label">MikroTik Model</div></div>
            <div class="snmp-info-card"><div class="snmp-info-value" data-field="uptime">-</div><div class="snmp-info-label">System uptime</div></div>
            <div class="snmp-info-card"><div class="snmp-info-value" data-field="version">-</div><div class="snmp-info-label">ROS version</div></div>
            <div class="snmp-info-card"><div class="snmp-info-value" data-field="license">-</div><div class="snmp-info-label">MikroTik License</div></div>
            <div class="snmp-info-card"><div class="snmp-info-value" data-field="temperature">-</div><div class="snmp-info-label">Temperature/Voltage</div></div>
            <div class="snmp-info-card"><div class="snmp-info-value" data-field="cpu_detail">-</div><div class="snmp-info-label">CPU Count / Load</div></div>
            <div class="snmp-info-card"><div class="snmp-info-value" data-field="memory_detail">-</div><div class="snmp-info-label">Memory terpakai/total</div></div>
            <div class="snmp-info-card"><div class="snmp-info-value" data-field="disk_detail">-</div><div class="snmp-info-label">Disk terpakai/total</div></div>
        </div>
    </section>

    <script>
        (() => {
            const root = document.getElementById(@js($domId));
            if (!root || root.dataset.ready === '1') return;
            root.dataset.ready = '1';

            const endpoint = root.dataset.endpoint;
            const status = root.querySelector('[data-snmp-status]');

            const setGauge = (key, value) => {
                const gauge = root.querySelector(`[data-gauge="${key}"]`);
                if (!gauge) return;
                const numeric = Math.max(0, Math.min(100, Number(value || 0)));
                gauge.style.setProperty('--value', numeric);
                const label = gauge.querySelector('.snmp-gauge-value');
                if (label) label.textContent = `${Math.round(numeric)}%`;
            };

            const setField = (key, value) => {
                const field = root.querySelector(`[data-field="${key}"]`);
                if (field) field.textContent = value || '-';
            };

            const render = (data) => {
                setGauge('cpu_load', data.cpu_load);
                setGauge('memory_used_percent', data.memory_used_percent);
                setGauge('disk_used_percent', data.disk_used_percent);
                ['identity', 'model', 'uptime', 'version', 'license', 'temperature', 'cpu_detail', 'memory_detail', 'disk_detail'].forEach((key) => setField(key, data[key]));
                status.dataset.state = data.status || 'unreachable';
                status.textContent = `${data.status === 'reachable' ? 'SNMP Online' : 'SNMP Offline'} / ${data.updated_at || '-'}`;
            };

            const fetchSnapshot = async () => {
                try {
                    const response = await fetch(endpoint, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    render(await response.json());
                } catch (error) {
                    status.dataset.state = 'unreachable';
                    status.textContent = 'SNMP Offline';
                }
            };

            root.querySelector('[data-snmp-refresh]')?.addEventListener('click', fetchSnapshot);
            fetchSnapshot();
            const timer = setInterval(() => {
                if (!document.body.contains(root)) {
                    clearInterval(timer);
                    return;
                }
                fetchSnapshot();
            }, 1000);
        })();
    </script>
</div>
