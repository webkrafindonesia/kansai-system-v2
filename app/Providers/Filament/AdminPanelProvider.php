<?php

namespace App\Providers\Filament;

use Filament\Pages\Dashboard;
use Filament\Widgets\AccountWidget;
use App\Filament\Widgets\DashboardSalesOrder;
use App\Filament\Widgets\DashboardProduct;
use App\Filament\Widgets\DashboardInvoice;
use App\Filament\Widgets\DashboardPurchase;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
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
use DiogoGPinto\AuthUIEnhancer\AuthUIEnhancerPlugin;
use Filament\Navigation\NavigationGroup;
use AlizHarb\ActivityLog\ActivityLogPlugin;
use FilipFonal\FilamentLogManager\FilamentLogManager;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use TomatoPHP\FilamentUsers\FilamentUsersPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->globalSearch(false)
            ->id('admin')
            ->path(env('FILAMENT_PATH'))
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->domain(env('FILAMENT_DOMAIN'))
            ->login()
            ->profile()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
                DashboardSalesOrder::class,
                DashboardProduct::class,
                DashboardInvoice::class,
                DashboardPurchase::class,
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
            ])
            ->readOnlyRelationManagersOnResourceViewPagesByDefault(false)
            ->maxContentWidth('full')
            ->sidebarCollapsibleOnDesktop()
            ->spa()
            ->plugins([
                ActivityLogPlugin::make(),
                FilamentLogManager::make(),
                FilamentShieldPlugin::make(),
                FilamentUsersPlugin::make(),
                AuthUIEnhancerPlugin::make()
                    ->showEmptyPanelOnMobile('top')
                    ->formPanelPosition('right')
                    ->formPanelWidth('40%')
                    ->emptyPanelBackgroundImageOpacity('70%')
                    ->formPanelBackgroundColor(Color::Zinc, '300')
                    ->emptyPanelBackgroundImageUrl('https://images.pexels.com/photos/466685/pexels-photo-466685.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2')
            ])
            ->theme(asset('css/filament/admin/theme.css'))
            ->navigationGroups([
                    NavigationGroup::make()
                        ->label('Penjualan')
                        ->collapsed()
                        ->icon('https://img.icons8.com/color/96/add-shopping-cart--v1.png'),
                    NavigationGroup::make()
                        ->label('Logistik')
                        ->collapsed()
                        ->icon('https://img.icons8.com/color/96/garage-closed.png'),
                    NavigationGroup::make()
                        ->label('Produk')
                        ->collapsed()
                        ->icon('https://img.icons8.com/color/96/network-cable.png'),
                    NavigationGroup::make()
                        ->label('Dokumen')
                        ->collapsed()
                        ->icon('https://img.icons8.com/color/96/purchase-order.png'),
                    NavigationGroup::make()
                        ->label('Karyawan')
                        ->collapsed()
                        ->icon('https://img.icons8.com/color/96/worker-male--v1.png'),
                    NavigationGroup::make()
                        ->label('Master Data')
                        ->collapsed()
                        ->icon('https://img.icons8.com/color/96/budget.png'),
                    NavigationGroup::make()
                        ->label('Laporan')
                        ->collapsed()
                        ->icon('https://img.icons8.com/color/96/overview-pages-2.png'),
                    NavigationGroup::make()
                        ->label('Kas Kecil')
                        ->collapsed()
                        ->icon('https://img.icons8.com/color/96/get-cash.png'),
                    NavigationGroup::make()
                        ->label('Sistem')
                        ->collapsed()
                        ->icon('https://img.icons8.com/color/96/security-configuration.png'),
                ])
            ->databaseNotifications()
            ->favicon(asset('images/favicon.png'))
            ->brandName('Kansai')
            ->brandLogo(asset('images/logo.jpeg'))
            ->databaseTransactions();
    }
}
