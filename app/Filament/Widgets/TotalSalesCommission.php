<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use App\Filament\Pages\SalesCommission;
use App\Models\Sales;

class TotalSalesCommission extends BaseWidget
{
    use InteractsWithPageFilters;
    use InteractsWithPageTable;

    protected static bool $isLazy = false;

     protected static bool $isDiscovered = false;

    protected function getTablePage(): string
    {
        return SalesCommission::class;
    }

    protected function getStats(): array
    {
        $data = $this->getPageTableQuery()->get();
        $salesOrders = $data->groupBy('sales_id');

        $stats = [];
        $colorOptions = ['primary', 'success', 'danger', 'warning', 'info'];
        $loop = 0;
        foreach($salesOrders as $sales_id => $salesOrder) {
            $sales_name = Sales::find($sales_id)->name;

            $stats[] = Stat::make($sales_name, 'Rp '.numberFormat(moneyFormat($salesOrder->sum('sales_commission'))))
                ->color($colorOptions[$loop % count($colorOptions)])
                ->description('Total Komisi dari '.$salesOrder->count().' Invoice')
                ->chart([7, 2, 10, 3, 15, 4, 17]);
            $loop++;
        }
        return $stats;
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
