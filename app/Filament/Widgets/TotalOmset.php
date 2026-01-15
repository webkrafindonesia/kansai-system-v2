<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use App\Models\SalesOrderItem;
use App\Filament\Pages\Omset;

class TotalOmset extends BaseWidget
{
    use InteractsWithPageFilters;
    use InteractsWithPageTable;

    protected static bool $isLazy = false;

     protected static bool $isDiscovered = false;

    protected function getTablePage(): string
    {
        return Omset::class;
    }

    protected function getStats(): array
    {
        $data = $this->getPageTableQuery()->get();

        return [
            Stat::make('Total Omset', 'Rp '.numberFormat(moneyFormat($data->sum('discounted_total_price'))))
                ->color('primary')
                ->description('Total Omset berdasarkan filter invoice date')
                ->chart([7, 2, 10, 3, 15, 4, 17]),
            Stat::make('Jumlah Qty', numberFormat($data->sum('total_qty')))
                ->color('success')
                ->description('Jumlah Qty dari semua item berdasarkan filter invoice date')
                ->chart([7, 2, 10, 3, 15, 4, 17]),
            Stat::make('Jumlah Item', numberFormat($data->count('product_id')))
                ->color('danger')
                ->description('Jumlah jenis item berdasarkan filter invoice date')
                ->chart([7, 2, 10, 3, 15, 4, 17]),
        ];
    }
}
