<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use App\Filament\Pages\Hutang;

class TotalHutang extends BaseWidget
{
    use InteractsWithPageFilters;
    use InteractsWithPageTable;

    protected static bool $isLazy = false;

     protected static bool $isDiscovered = false;

    protected function getTablePage(): string
    {
        return Hutang::class;
    }

    protected function getStats(): array
    {
        $data = $this->getPageTableQuery()->get();

        return [
            Stat::make('Total Hutang', 'Rp '.numberFormat(moneyFormat($data->sum('total_price'))))
                ->color('primary')
                ->description('Total Hutang berdasarkan filter tanggal pembelian')
                ->chart([7, 2, 10, 3, 15, 4, 17]),
        ];
    }
}
