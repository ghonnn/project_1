<?php

namespace App\Providers\Filament;

use App\Filament\Pages\AdminUsers;
use App\Filament\Pages\AppSetting;
use App\Filament\Pages\PlatformDashboard;
use App\Filament\Resources\AuditLogResource;
use App\Filament\Resources\TenantResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class OwnerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return AdminPanelProvider::applyNexAppearance($panel
            ->id('owner')
            ->path('owner')
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
            ->navigationGroups([
                NavigationGroup::make('Platform')->icon('heroicon-o-building-office-2'),
                NavigationGroup::make('Keamanan & Audit')->icon('heroicon-o-shield-check'),
                NavigationGroup::make('Pengaturan')->icon('heroicon-o-cog-6-tooth'),
            ])
            ->pages([
                PlatformDashboard::class,
                AdminUsers::class,
                AppSetting::class,
            ])
            ->resources([
                TenantResource::class,
                AuditLogResource::class,
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
            ]));
    }
}
