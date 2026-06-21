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
                'primary' => Color::Violet,
                'danger' => Color::Rose,
                'gray' => Color::Slate,
                'info' => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
            ])
            ->defaultThemeMode(ThemeMode::Dark)
            ->sidebarCollapsibleOnDesktop()
            ->spa()
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString(<<<'HTML'
                    <style>
                        html {
                            font-size: 13px;
                        }

                        .fi-sidebar-group-label,
                        .fi-sidebar-item-label {
                            font-size: 14px !important;
                            line-height: 1.15 !important;
                        }

                        .fi-sidebar-item-button {
                            min-height: 26px !important;
                            padding-block: 1px !important;
                        }

                        .fi-sidebar-group,
                        .fi-sidebar-group-items {
                            row-gap: 1px !important;
                        }

                        .fi-sidebar-header {
                            min-height: 56px !important;
                            padding-inline: 14px !important;
                        }

                        .fi-sidebar-header .fi-logo {
                            color: #e5e7eb !important;
                            font-size: 16px !important;
                            font-weight: 800 !important;
                            letter-spacing: 0 !important;
                            white-space: normal !important;
                        }

                        .fi-main {
                            max-width: none !important;
                            width: 100% !important;
                            padding-inline: 18px !important;
                        }

                        .fi-page {
                            max-width: none !important;
                        }

                        .fi-ta,
                        .fi-section {
                            width: 100%;
                        }

                        .nex-topbar-brand {
                            display: flex;
                            align-items: center;
                            gap: 24px;
                            min-width: 430px;
                            padding-left: 8px;
                        }

                        .nex-brand-text {
                            color: #f8fafc;
                            font-size: 15px;
                            font-weight: 800;
                            white-space: nowrap;
                        }

                        .nex-server-time {
                            color: #e5e7eb;
                            font-size: 14px;
                            font-weight: 700;
                            white-space: nowrap;
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
