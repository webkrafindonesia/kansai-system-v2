<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\SalesOrder;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class DashboardSalesOrder extends BaseWidget
{
    use HasWidgetShield;

    protected function getHeading(): ?string
    {
        return 'Overview Sales Order';
    }

    protected function getStats(): array
    {
        $salesOrder = SalesOrder::query();
        $withoutPO = $salesOrder->clone()->whereNull('delivery_order_id')->count();
        // $waitingProduction = $salesOrder->clone()
        //                         ->whereNotNull('purchaseorder_no')
        //                         ->where('production_start',0)
        //                         ->count();
        // $onProduction = $salesOrder->clone()
        //                         ->where('production_start',1)
        //                         ->where('production_end',0)
        //                         ->count();
        $notDelivered = $salesOrder->clone()
                                ->whereHas('deliveryOrder',function($q){
                                    $q->whereNull('processed_at');
                                })
                                ->count();

        return [
            Stat::make('Belum Dikunci', $withoutPO)
                ->color('primary')
                ->description('Sales Order yang belum dilakukan perakitan atau packing.')
                ->chart([7, 2, 10, 3, 15, 4, 17]),
            // Stat::make('Waiting Production', $waitingProduction)
            //     ->color('danger')
            //     ->description('Sales Order yang menunggu proses perakitan')
            //     ->chart([7, 2, 10, 3, 15, 4, 17]),
            // Stat::make('On Production', $onProduction)
            //     ->color('success')
            //     ->description('Sales Order yang masih dalam proses perakitan')
            //     ->chart([7, 2, 10, 3, 15, 4, 17]),
            Stat::make('Belum Kirim', $notDelivered)
                ->color('info')
                ->description('Sales Order yang sudah selesai dirakit (atau tidak butuh perakitan) namun belum dikirim ke customer')
                ->chart([7, 2, 10, 3, 15, 4, 17]),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}
