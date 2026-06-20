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
use App\Models\Tenant;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('NEX ISP PLATFORM')
            ->brandLogo(fn (): string => self::brandLogo())
            ->brandLogoHeight('46px')
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

                        .fi-sidebar-header img {
                            max-height: 46px !important;
                            max-width: 200px !important;
                            object-fit: contain;
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
                            gap: 28px;
                            min-width: 340px;
                            padding-left: 8px;
                        }

                        .nex-topbar-brand img {
                            width: 72px;
                            height: 40px;
                            object-fit: contain;
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

    private static function brandLogo(): string
    {
        $path = Tenant::query()->whereNotNull('logo_path')->value('logo_path');

        if ($path) {
            return Storage::disk('public')->url($path);
        }

        return 'data:image/svg+xml;base64,'.base64_encode(<<<'SVG'
            <svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200">
                <rect width="200" height="200" rx="28" fill="#0f172a"/>
                <circle cx="100" cy="62" r="31" fill="#0ea5e9"/>
                <path d="M51 68c20-24 45 25 69 0 12-12 22-14 32-9" fill="none" stroke="#fff" stroke-width="10" stroke-linecap="round"/>
                <text x="100" y="123" text-anchor="middle" font-family="Arial, sans-serif" font-size="24" font-weight="700" fill="#fff">NEX ISP</text>
                <text x="100" y="149" text-anchor="middle" font-family="Arial, sans-serif" font-size="17" font-weight="700" fill="#38bdf8">PLATFORM</text>
            </svg>
        SVG);
    }

    private static function topbarBrand(): string
    {
        $logo = e(self::brandLogo());
        $time = now('Asia/Jakarta')->format('d/m/Y H:i:s').' WIB';

        return <<<HTML
            <div class="nex-topbar-brand">
                <img src="{$logo}" alt="NEX ISP PLATFORM">
                <div class="nex-server-time">WAKTU SERVER : {$time}</div>
            </div>
        HTML;
    }
}
