<?php

namespace App\Providers\Filament;

use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('NEX ISP Platform')
            ->login()
            ->colors([
                'primary' => Color::Emerald,
                'danger' => Color::Rose,
                'gray' => Color::Slate,
                'info' => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
            ])
            ->defaultThemeMode(ThemeMode::Light)
            ->darkMode(false)
            ->sidebarCollapsibleOnDesktop()
            ->spa()
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString(<<<'HTML'
                    <style>
                        :root {
                            --nex-emerald: #059669;
                            --nex-emerald-dark: #047857;
                            --nex-emerald-soft: #ecfdf5;
                            --nex-ink: #111827;
                            --nex-muted: #64748b;
                            --nex-border: #e5e7eb;
                            --nex-surface: #ffffff;
                            --nex-page: #f8fafc;
                            color-scheme: light;
                        }

                        html {
                            font-size: 14px;
                        }

                        body,
                        .fi-body,
                        .dark body,
                        .dark .fi-body {
                            background: var(--nex-page) !important;
                            color: var(--nex-ink) !important;
                            font-family: "Segoe UI", Inter, ui-sans-serif, system-ui, sans-serif !important;
                        }

                        .fi-layout,
                        .dark .fi-layout {
                            background: var(--nex-page) !important;
                        }

                        .fi-sidebar,
                        .dark .fi-sidebar {
                            background: #ffffff !important;
                            border-right: 1px solid var(--nex-border) !important;
                            box-shadow: 1px 0 0 rgba(15, 23, 42, 0.02) !important;
                        }

                        .fi-sidebar-group-label,
                        .fi-sidebar-item-label {
                            font-size: 15px !important;
                            line-height: 1.35 !important;
                            letter-spacing: 0 !important;
                        }

                        .fi-sidebar-group-label {
                            color: #64748b !important;
                            font-size: 12px !important;
                            font-weight: 700 !important;
                            text-transform: uppercase;
                        }

                        .fi-sidebar-item-label,
                        .fi-sidebar-item-icon {
                            color: #334155 !important;
                        }

                        .fi-sidebar-item-button {
                            min-height: 40px !important;
                            padding-block: 8px !important;
                            padding-inline: 10px !important;
                            border-radius: 6px !important;
                        }

                        .fi-sidebar-item-button:hover {
                            background: var(--nex-emerald-soft) !important;
                        }

                        .fi-sidebar-item-active > .fi-sidebar-item-button,
                        .fi-sidebar-item-button[aria-current="page"] {
                            background: var(--nex-emerald-soft) !important;
                            border-left: 3px solid var(--nex-emerald) !important;
                            color: var(--nex-emerald-dark) !important;
                        }

                        .fi-sidebar-item-active .fi-sidebar-item-label,
                        .fi-sidebar-item-active .fi-sidebar-item-icon {
                            color: var(--nex-emerald-dark) !important;
                            font-weight: 700 !important;
                        }

                        .fi-sidebar-group,
                        .fi-sidebar-group-items {
                            row-gap: 5px !important;
                        }

                        .fi-sidebar-header {
                            min-height: 54px !important;
                            padding-inline: 16px !important;
                            border-bottom: 1px solid var(--nex-border) !important;
                        }

                        .fi-sidebar-header .fi-logo {
                            color: var(--nex-ink) !important;
                            font-size: 16px !important;
                            font-weight: 800 !important;
                            letter-spacing: 0 !important;
                            white-space: normal !important;
                        }

                        .fi-main {
                            max-width: none !important;
                            width: 100% !important;
                            padding-inline: 20px !important;
                            background: var(--nex-page) !important;
                        }

                        .fi-page {
                            max-width: none !important;
                        }

                        .fi-ta,
                        .fi-section {
                            width: 100%;
                        }

                        .fi-topbar,
                        .dark .fi-topbar {
                            background: #ffffff !important;
                            border-bottom: 1px solid var(--nex-border) !important;
                            box-shadow: none !important;
                        }

                        .fi-header-heading {
                            color: var(--nex-ink) !important;
                            font-size: 22px !important;
                            font-weight: 700 !important;
                            letter-spacing: 0 !important;
                        }

                        .fi-section,
                        .fi-ta-ctn,
                        .fi-modal-window,
                        .fi-wi-stats-overview-stat,
                        .dark .fi-section,
                        .dark .fi-ta-ctn,
                        .dark .fi-modal-window,
                        .dark .fi-wi-stats-overview-stat {
                            background: var(--nex-surface) !important;
                            border: 1px solid var(--nex-border) !important;
                            border-radius: 8px !important;
                            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
                        }

                        .fi-section-header,
                        .dark .fi-section-header {
                            background: #ffffff !important;
                            border-bottom: 1px solid #eef2f7 !important;
                        }

                        .fi-ta-header,
                        .fi-ta-content,
                        .fi-ta-footer,
                        .dark .fi-ta-header,
                        .dark .fi-ta-content,
                        .dark .fi-ta-footer {
                            background: #ffffff !important;
                        }

                        .fi-ta-header-cell,
                        .fi-ta-cell,
                        .dark .fi-ta-header-cell,
                        .dark .fi-ta-cell {
                            border-color: #e2e8f0 !important;
                            border-bottom-width: 1px !important;
                        }

                        .fi-ta-row,
                        .dark .fi-ta-row {
                            background: #ffffff !important;
                        }

                        .fi-ta-row:nth-child(even),
                        .dark .fi-ta-row:nth-child(even) {
                            background: #fbfdff !important;
                        }

                        .fi-ta-row:hover,
                        .dark .fi-ta-row:hover {
                            background: #f0fdf4 !important;
                        }

                        .fi-ta,
                        .fi-ta-header-cell,
                        .fi-ta-cell,
                        .fi-ta-text,
                        .fi-ta-header-cell-label,
                        .fi-wi-stats-overview-stat,
                        .fi-wi-stats-overview-stat *:not(svg):not(path),
                        .dark .fi-ta,
                        .dark .fi-ta-header-cell,
                        .dark .fi-ta-cell,
                        .dark .fi-ta-text,
                        .dark .fi-ta-header-cell-label,
                        .dark .fi-wi-stats-overview-stat,
                        .dark .fi-wi-stats-overview-stat *:not(svg):not(path) {
                            color: #1f2937 !important;
                        }

                        .fi-ta-header-cell-label {
                            color: #475569 !important;
                            font-size: 14px !important;
                            font-weight: 800 !important;
                            text-transform: none;
                        }

                        .fi-ta-table {
                            border-collapse: separate !important;
                            border-spacing: 0 !important;
                        }

                        .fi-ta-content {
                            border-top: 1px solid #e2e8f0 !important;
                        }

                        .fi-input-wrp,
                        .fi-fo-select,
                        .choices__inner {
                            background: #ffffff !important;
                            border: 1px solid #94a3b8 !important;
                            border-radius: 6px !important;
                            box-shadow: none !important;
                        }

                        .fi-input-wrp:not(:focus-within):hover,
                        .choices:not(.is-focused):hover .choices__inner {
                            border-color: #64748b !important;
                        }

                        .fi-input,
                        .fi-select-input,
                        .fi-textarea,
                        .choices__inner,
                        .choices__item,
                        .fi-fo-field-wrp-label,
                        .fi-fo-field-wrp-helper-text,
                        .fi-btn,
                        .fi-dropdown-list-item,
                        .fi-ta-cell,
                        .fi-ta-text,
                        .fi-ta-summary-row,
                        .fi-pagination,
                        .fi-pagination * {
                            font-size: 14px !important;
                            letter-spacing: 0 !important;
                        }

                        .fi-btn,
                        .fi-ac-btn-action,
                        .fi-ta-actions .fi-link,
                        .fi-dropdown-list-item,
                        .nex-action-button,
                        .nex-toolbar-button {
                            display: inline-flex !important;
                            align-items: center !important;
                            justify-content: center !important;
                            min-height: 38px !important;
                            min-width: 92px !important;
                            padding: 9px 14px !important;
                            border-radius: 7px !important;
                            font-size: 14px !important;
                            font-weight: 700 !important;
                            line-height: 1.1 !important;
                            white-space: nowrap !important;
                        }

                        .fi-input,
                        .fi-select-input,
                        .fi-textarea {
                            color: #0f172a !important;
                            min-height: 36px !important;
                        }

                        .fi-input::placeholder,
                        .fi-textarea::placeholder {
                            color: #64748b !important;
                            opacity: 1 !important;
                        }

                        .fi-input:disabled,
                        .fi-select-input:disabled,
                        .fi-textarea:disabled,
                        .fi-input[readonly],
                        .fi-textarea[readonly] {
                            color: #0f172a !important;
                            -webkit-text-fill-color: #0f172a !important;
                            opacity: 1 !important;
                            background: #f8fafc !important;
                        }

                        .fi-fo-field-wrp {
                            gap: 6px !important;
                        }

                        .fi-fo-field-wrp-label,
                        .fi-fo-field-wrp-label span {
                            color: #0f172a !important;
                            font-weight: 650 !important;
                        }

                        .fi-section-content {
                            background-image: linear-gradient(to right, transparent calc(50% - 1px), #e2e8f0 calc(50% - 1px), #e2e8f0 50%, transparent 50%);
                            background-size: 100% 100%;
                            background-repeat: no-repeat;
                        }

                        .fi-input-wrp:focus-within,
                        .choices.is-focused .choices__inner {
                            border-color: var(--nex-emerald) !important;
                            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.12) !important;
                        }

                        .fi-btn {
                            border-radius: 6px !important;
                            font-weight: 600 !important;
                            box-shadow: none !important;
                        }

                        .nex-dashboard-grid {
                            display: grid;
                            grid-template-columns: repeat(3, minmax(0, 1fr));
                            gap: 16px;
                        }

                        .nex-metric-card,
                        .nex-panel-card {
                            border: 1px solid #dbe3ef;
                            border-radius: 10px;
                            background: #ffffff;
                            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
                        }

                        .nex-metric-card {
                            position: relative;
                            overflow: hidden;
                            padding: 18px;
                            min-height: 138px;
                        }

                        .nex-metric-card::after {
                            content: "";
                            position: absolute;
                            right: -24px;
                            bottom: -28px;
                            width: 120px;
                            height: 120px;
                            border-radius: 999px;
                            background: var(--metric-soft, #ecfdf5);
                        }

                        .nex-metric-head,
                        .nex-metric-body,
                        .nex-metric-foot {
                            position: relative;
                            z-index: 1;
                        }

                        .nex-metric-head {
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            gap: 12px;
                        }

                        .nex-metric-icon {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            width: 42px;
                            height: 42px;
                            border-radius: 9px;
                            background: var(--metric-soft, #ecfdf5);
                            color: var(--metric-color, #059669);
                        }

                        .nex-metric-label {
                            color: #64748b;
                            font-size: 14px;
                            font-weight: 700;
                        }

                        .nex-metric-value {
                            margin-top: 18px;
                            color: #0f172a;
                            font-size: 28px;
                            font-weight: 800;
                            line-height: 1;
                        }

                        .nex-metric-foot {
                            margin-top: 14px;
                            color: #64748b;
                            font-size: 13px;
                            font-weight: 650;
                        }

                        .nex-panel-card {
                            overflow: hidden;
                        }

                        .nex-panel-header {
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            gap: 12px;
                            padding: 16px 18px;
                            border-bottom: 1px solid #e2e8f0;
                        }

                        .nex-panel-title {
                            color: #0f172a;
                            font-size: 15px;
                            font-weight: 800;
                        }

                        .nex-panel-subtitle {
                            color: #64748b;
                            font-size: 13px;
                            font-weight: 600;
                        }

                        .nex-progress-row {
                            display: grid;
                            gap: 8px;
                        }

                        .nex-progress-label {
                            display: flex;
                            justify-content: space-between;
                            gap: 12px;
                            color: #334155;
                            font-size: 14px;
                            font-weight: 700;
                        }

                        .nex-progress-track {
                            height: 9px;
                            overflow: hidden;
                            border-radius: 999px;
                            background: #e2e8f0;
                        }

                        .nex-progress-fill {
                            height: 100%;
                            border-radius: inherit;
                            background: var(--progress-color, #059669);
                        }

                        .nex-finance-card,
                        .nex-finance-table {
                            border: 1px solid #dbe3ef;
                            border-radius: 10px;
                            background: #ffffff;
                            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
                        }

                        .nex-finance-table table {
                            border-collapse: separate;
                            border-spacing: 0;
                        }

                        .nex-finance-table th,
                        .nex-finance-table td {
                            border-bottom: 1px solid #e2e8f0;
                            color: #0f172a;
                            font-size: 14px;
                        }

                        @media (max-width: 1180px) {
                            .nex-dashboard-grid {
                                grid-template-columns: repeat(2, minmax(0, 1fr));
                            }
                        }

                        @media (max-width: 760px) {
                            .nex-dashboard-grid {
                                grid-template-columns: 1fr;
                            }
                        }

                        .fi-badge {
                            border-radius: 999px !important;
                            font-weight: 700 !important;
                        }

                        .fi-badge * {
                            color: inherit !important;
                        }

                        .nex-topbar-brand {
                            display: flex;
                            align-items: center;
                            gap: 24px;
                            min-width: 430px;
                            padding-left: 8px;
                        }

                        .nex-app-logo {
                            display: inline-flex;
                            align-items: center;
                            gap: 10px;
                            color: var(--nex-ink);
                            font-size: 15px;
                            font-weight: 800;
                            white-space: nowrap;
                        }

                        .nex-logo-mark {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            width: 34px;
                            height: 28px;
                            border-radius: 6px;
                            background: var(--nex-emerald);
                            color: #ffffff;
                            font-size: 11px;
                            font-weight: 900;
                            letter-spacing: 0;
                        }

                        .nex-brand-text {
                            color: var(--nex-ink);
                            font-size: 15px;
                            font-weight: 800;
                            white-space: nowrap;
                        }

                        .nex-server-time {
                            color: var(--nex-muted);
                            font-size: 14px;
                            font-weight: 700;
                            white-space: nowrap;
                        }

                        @media (max-width: 768px) {
                            .nex-topbar-brand {
                                min-width: 0;
                                gap: 12px;
                            }

                            .nex-server-time {
                                display: none;
                            }
                        }
                    </style>
                    <script>
                        (() => {
                            const pad = (value) => String(value).padStart(2, '0');
                            const formatJakartaTime = (date) => {
                                const parts = new Intl.DateTimeFormat('en-GB', {
                                    timeZone: 'Asia/Jakarta',
                                    year: 'numeric',
                                    month: '2-digit',
                                    day: '2-digit',
                                    hour: '2-digit',
                                    minute: '2-digit',
                                    second: '2-digit',
                                    hour12: false,
                                }).formatToParts(date).reduce((carry, part) => {
                                    carry[part.type] = part.value;
                                    return carry;
                                }, {});

                                return `${parts.day}/${parts.month}/${parts.year} ${parts.hour}:${parts.minute}:${parts.second} WIB`;
                            };

                            const bootClock = () => {
                                document.querySelectorAll('[data-nex-server-time]').forEach((node) => {
                                    if (node.dataset.clockReady === '1') {
                                        return;
                                    }

                                    node.dataset.clockReady = '1';
                                    const startedAt = new Date(node.dataset.nexServerTime);
                                    const clientStartedAt = Date.now();
                                    const tick = () => {
                                        node.textContent = `WAKTU SERVER : ${formatJakartaTime(new Date(startedAt.getTime() + Date.now() - clientStartedAt))}`;
                                    };

                                    tick();
                                    setInterval(tick, 1000);
                                });
                            };

                            document.addEventListener('DOMContentLoaded', bootClock);
                            document.addEventListener('livewire:navigated', bootClock);
                        })();
                    </script>
                HTML)
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_START,
                fn (): HtmlString => new HtmlString(self::topbarBrand())
            )
            ->navigationGroups([
                NavigationGroup::make('Platform')->icon('heroicon-o-building-office-2'),
                NavigationGroup::make('Partner')->icon('heroicon-o-user-group'),
                NavigationGroup::make('Pelanggan')->icon('heroicon-o-users'),
                NavigationGroup::make('Katalog')->icon('heroicon-o-tag'),
                NavigationGroup::make('Voucher')->icon('heroicon-o-wifi'),
                NavigationGroup::make('Langganan')->icon('heroicon-o-user-group'),
                NavigationGroup::make('Map')->icon('heroicon-o-map'),
                NavigationGroup::make('Jaringan')->icon('heroicon-o-server-stack'),
                NavigationGroup::make('Radius')->icon('heroicon-o-key'),
                NavigationGroup::make('Tiket')->icon('heroicon-o-ticket'),
                NavigationGroup::make('Billing')->icon('heroicon-o-credit-card'),
                NavigationGroup::make('Pengaturan')->icon('heroicon-o-cog-6-tooth'),
                NavigationGroup::make('Keamanan & Audit')->icon('heroicon-o-shield-check')->collapsed(),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    private static function topbarBrand(): string
    {
        $time = e(now('Asia/Jakarta')->format('d/m/Y H:i:s').' WIB');
        $timeIso = e(now('Asia/Jakarta')->format('c'));

        return <<<HTML
            <div class="nex-topbar-brand">
                <div class="nex-app-logo"><span class="nex-logo-mark">NEX</span><span class="nex-brand-text">NEX ISP Platform</span></div>
                <div class="nex-server-time" data-nex-server-time="{$timeIso}">WAKTU SERVER : {$time}</div>
            </div>
        HTML;
    }

    public static function applyNexAppearance(Panel $panel): Panel
    {
        return $panel
            ->defaultThemeMode(ThemeMode::Light)
            ->darkMode(false)
            ->sidebarCollapsibleOnDesktop()
            ->spa()
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString(<<<'HTML'
                    <style>
                        :root {
                            --nex-emerald: #059669;
                            --nex-emerald-dark: #047857;
                            --nex-emerald-soft: #ecfdf5;
                            --nex-border: #dbe3ef;
                            --nex-page: #f8fafc;
                            color-scheme: light;
                        }

                        html { font-size: 14px; }
                        body, .fi-body, .fi-layout { background: var(--nex-page) !important; color: #0f172a !important; }
                        .fi-topbar, .fi-sidebar, .fi-section, .fi-ta-ctn, .fi-modal-window, .fi-wi-stats-overview-stat {
                            background: #ffffff !important;
                            border-color: var(--nex-border) !important;
                        }

                        .fi-sidebar { border-right: 1px solid var(--nex-border) !important; }
                        .fi-sidebar-header { border-bottom: 1px solid var(--nex-border) !important; }
                        .fi-sidebar-header .fi-logo { color: #0f172a !important; font-size: 16px !important; font-weight: 800 !important; }
                        .fi-sidebar-item-label, .fi-sidebar-item-icon { color: #334155 !important; font-size: 15px !important; }
                        .fi-sidebar-group-label { color: #64748b !important; font-size: 12px !important; font-weight: 800 !important; text-transform: uppercase; }
                        .fi-sidebar-item-button { border-radius: 6px !important; min-height: 40px !important; padding-block: 8px !important; padding-inline: 10px !important; }
                        .fi-sidebar-item-button:hover, .fi-sidebar-item-active > .fi-sidebar-item-button, .fi-sidebar-item-button[aria-current="page"] {
                            background: var(--nex-emerald-soft) !important;
                            color: var(--nex-emerald-dark) !important;
                        }

                        .fi-input-wrp, .fi-fo-select, .choices__inner {
                            background: #ffffff !important;
                            border: 1px solid #94a3b8 !important;
                            border-radius: 6px !important;
                            box-shadow: none !important;
                        }

                        .fi-input, .fi-select-input, .fi-textarea, .choices__item, .fi-btn, .fi-dropdown-list-item, .fi-ta-cell, .fi-ta-text, .fi-pagination, .fi-pagination * {
                            color: #0f172a !important;
                            font-size: 14px !important;
                            letter-spacing: 0 !important;
                        }

                        .fi-ta-header-cell, .fi-ta-cell { border-color: #dbe3ef !important; border-bottom-width: 1px !important; }
                        .fi-ta-row:nth-child(even) { background: #fbfdff !important; }
                        .fi-ta-row:hover { background: #f0fdf4 !important; }
                        .fi-btn, .nex-action-button, .nex-toolbar-button { display: inline-flex !important; align-items: center !important; justify-content: center !important; min-height: 38px !important; min-width: 92px !important; padding: 9px 14px !important; border-radius: 7px !important; box-shadow: none !important; font-size: 14px !important; font-weight: 700 !important; white-space: nowrap !important; }
                        .nex-topbar-brand { display: flex; align-items: center; gap: 24px; min-width: 430px; padding-left: 8px; }
                        .nex-app-logo { display: inline-flex; align-items: center; gap: 10px; color: #0f172a; font-size: 15px; font-weight: 800; white-space: nowrap; }
                        .nex-logo-mark { display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 28px; border-radius: 6px; background: var(--nex-emerald); color: #ffffff; font-size: 11px; font-weight: 900; }
                        .nex-server-time { color: #64748b; font-size: 14px; font-weight: 700; white-space: nowrap; }
                    </style>
                    <script>
                        (() => {
                            const formatJakartaTime = (date) => {
                                const parts = new Intl.DateTimeFormat('en-GB', { timeZone: 'Asia/Jakarta', year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false }).formatToParts(date).reduce((carry, part) => {
                                    carry[part.type] = part.value;
                                    return carry;
                                }, {});
                                return `${parts.day}/${parts.month}/${parts.year} ${parts.hour}:${parts.minute}:${parts.second} WIB`;
                            };
                            const bootClock = () => document.querySelectorAll('[data-nex-server-time]').forEach((node) => {
                                if (node.dataset.clockReady === '1') return;
                                node.dataset.clockReady = '1';
                                const startedAt = new Date(node.dataset.nexServerTime);
                                const clientStartedAt = Date.now();
                                const tick = () => node.textContent = `WAKTU SERVER : ${formatJakartaTime(new Date(startedAt.getTime() + Date.now() - clientStartedAt))}`;
                                tick();
                                setInterval(tick, 1000);
                            });
                            document.addEventListener('DOMContentLoaded', bootClock);
                            document.addEventListener('livewire:navigated', bootClock);
                        })();
                    </script>
                HTML)
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_START,
                fn (): HtmlString => new HtmlString(self::topbarBrand())
            );
    }
}
