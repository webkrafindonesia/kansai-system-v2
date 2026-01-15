<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Product;
use DB;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class DashboardProduct extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 3;

    protected function getHeading(): ?string
    {
        return 'Overview Product';
    }

    protected function getStats(): array
    {
        $count = Product::select('id','safety_stock')
                        ->where('safety_stock', '>', 0)
                        ->withSum('stocks as total_stock', 'qty')
                        ->having('total_stock', '<', DB::raw('safety_stock'))
                        ->groupBy('id','safety_stock','total_stock')
                        ->count();
        return [
            Stat::make('Under Safety Stock', $count)
                ->color('danger')
                ->description('Produk yang perlu di reorder')
                ->chart([7, 2, 10, 3, 15, 4, 17]),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}
