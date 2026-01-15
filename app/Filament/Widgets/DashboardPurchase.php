<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Purchase;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class DashboardPurchase extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 4;

    protected function getHeading(): ?string
    {
        return 'Overview Pembelian Supplier';
    }

    protected function getStats(): array
    {
        $purchases = Purchase::query()
                        ->where('payment_status','Belum Lunas');
        $notPaid = $purchases->count();

        return [
            Stat::make('Belum Lunas', $notPaid)
                ->color('primary')
                ->description('Total Tagihan Supplier yang belum lunas')
                ->chart([7, 2, 10, 3, 15, 4, 17]),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}
