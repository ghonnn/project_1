<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class IspPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return AdminPanelProvider::applyNexAppearance($panel
            ->id('isp')
            ->path('isp')
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
            ->sidebarCollapsibleOnDesktop()
            ->spa()
            ->navigationGroups([
                NavigationGroup::make('Pelanggan')->icon('heroicon-o-users'),
                NavigationGroup::make('Partner')->icon('heroicon-o-user-group'),
                NavigationGroup::make('Langganan')->icon('heroicon-o-user-group'),
                NavigationGroup::make('Katalog')->icon('heroicon-o-tag'),
                NavigationGroup::make('Voucher')->icon('heroicon-o-wifi'),
                NavigationGroup::make('Jaringan')->icon('heroicon-o-server-stack'),
                NavigationGroup::make('Radius')->icon('heroicon-o-key'),
                NavigationGroup::make('Map')->icon('heroicon-o-map'),
                NavigationGroup::make('Tiket')->icon('heroicon-o-ticket'),
                NavigationGroup::make('Billing')->icon('heroicon-o-credit-card'),
                NavigationGroup::make('Pengaturan')->icon('heroicon-o-cog-6-tooth'),
            ])
            ->pages([
                \App\Filament\Pages\Dashboard::class,
                \App\Filament\Pages\TransactionReport::class,
                \App\Filament\Pages\VoucherProfiles::class,
                \App\Filament\Pages\VoucherStock::class,
                \App\Filament\Pages\SoldVouchers::class,
                \App\Filament\Pages\OnlineVouchers::class,
                \App\Filament\Pages\VoucherRecap::class,
                \App\Filament\Pages\VoucherTemplate::class,
                \App\Filament\Pages\RouterScriptGenerator::class,
                \App\Filament\Pages\Odp::class,
                \App\Filament\Pages\Olt::class,
                \App\Filament\Pages\GenieAcs::class,
                \App\Filament\Pages\MapCustomers::class,
                \App\Filament\Pages\MapOdp::class,
                \App\Filament\Pages\BalanceMutation::class,
                \App\Filament\Pages\TopupBalance::class,
                \App\Filament\Pages\DiscountCoupon::class,
                \App\Filament\Pages\WhatsappSetting::class,
                \App\Filament\Pages\ToolsPage::class,
                \App\Filament\Pages\AppSetting::class,
            ])
            ->resources([
                \App\Filament\Resources\CustomerResource::class,
                \App\Filament\Resources\MitraResource::class,
                \App\Filament\Resources\ServiceResource::class,
                \App\Filament\Resources\OnlineSubscriptionResource::class,
                \App\Filament\Resources\StoppedSubscriptionResource::class,
                \App\Filament\Resources\ProductResource::class,
                \App\Filament\Resources\ServiceCategoryResource::class,
                \App\Filament\Resources\ServicePlanChangeResource::class,
                \App\Filament\Resources\RouterResource::class,
                \App\Filament\Resources\RouterInterfaceResource::class,
                \App\Filament\Resources\ServiceRouterMappingResource::class,
                \App\Filament\Resources\RouterScriptTemplateResource::class,
                \App\Filament\Resources\RadiusServerResource::class,
                \App\Filament\Resources\NasDeviceResource::class,
                \App\Filament\Resources\RadiusProfileResource::class,
                \App\Filament\Resources\RadiusUserResource::class,
                \App\Filament\Resources\TicketResource::class,
                \App\Filament\Resources\InvoiceResource::class,
                \App\Filament\Resources\PaidInvoiceResource::class,
                \App\Filament\Resources\PaymentResource::class,
                \App\Filament\Resources\ServiceAddonResource::class,
            ])
            ->widgets([
                Widgets\AccountWidget::class,
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
