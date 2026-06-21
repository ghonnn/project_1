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
                            font-size: 13px;
                        }

                        body,
                        .fi-body {
                            background: var(--nex-page) !important;
                            color: var(--nex-ink) !important;
                            font-family: "Segoe UI", Inter, ui-sans-serif, system-ui, sans-serif !important;
                        }

                        .fi-layout {
                            background: var(--nex-page) !important;
                        }

                        .fi-sidebar {
                            background: #ffffff !important;
                            border-right: 1px solid var(--nex-border) !important;
                            box-shadow: 1px 0 0 rgba(15, 23, 42, 0.02) !important;
                        }

                        .fi-sidebar-group-label,
                        .fi-sidebar-item-label {
                            font-size: 13px !important;
                            line-height: 1.2 !important;
                            letter-spacing: 0 !important;
                        }

                        .fi-sidebar-group-label {
                            color: #64748b !important;
                            font-size: 11px !important;
                            font-weight: 700 !important;
                            text-transform: uppercase;
                        }

                        .fi-sidebar-item-label,
                        .fi-sidebar-item-icon {
                            color: #334155 !important;
                        }

                        .fi-sidebar-item-button {
                            min-height: 32px !important;
                            padding-block: 4px !important;
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
                            row-gap: 3px !important;
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

                        .fi-topbar {
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
                        .fi-modal-window {
                            background: var(--nex-surface) !important;
                            border: 1px solid var(--nex-border) !important;
                            border-radius: 8px !important;
                            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
                        }

                        .fi-section-header {
                            background: #ffffff !important;
                            border-bottom: 1px solid #eef2f7 !important;
                        }

                        .fi-ta-header,
                        .fi-ta-content,
                        .fi-ta-footer {
                            background: #ffffff !important;
                        }

                        .fi-ta-header-cell,
                        .fi-ta-cell {
                            border-color: #eef2f7 !important;
                        }

                        .fi-ta-row:hover {
                            background: #f8fafc !important;
                        }

                        .fi-input-wrp,
                        .fi-fo-select,
                        .choices__inner {
                            background: #ffffff !important;
                            border-color: #d1d5db !important;
                            border-radius: 6px !important;
                            box-shadow: none !important;
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

                        .fi-badge {
                            border-radius: 999px !important;
                            font-weight: 700 !important;
                        }

                        .nex-topbar-brand {
                            display: flex;
                            align-items: center;
                            gap: 24px;
                            min-width: 430px;
                            padding-left: 8px;
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
                HTML)
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_START,
                fn (): HtmlString => new HtmlString(self::topbarBrand())
            )
            ->navigationGroups([
                NavigationGroup::make('Platform')->icon('heroicon-o-building-office-2'),
                NavigationGroup::make('Mitra')->icon('heroicon-o-user-group'),
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

        return <<<HTML
            <div class="nex-topbar-brand">
                <div class="nex-brand-text">NEX ISP Platform</div>
                <div class="nex-server-time">WAKTU SERVER : {$time}</div>
            </div>
        HTML;
    }
}
