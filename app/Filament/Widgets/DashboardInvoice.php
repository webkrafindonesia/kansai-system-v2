<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\SalesOrder;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class DashboardInvoice extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 2;

    protected function getHeading(): ?string
    {
        return 'Overview Invoice';
    }

    protected function getStats(): array
    {
        $invoices = SalesOrder::query()
                        ->whereNotNull('delivery_order_id')
                        ->whereNotNull('invoice_no')
                        ->where('invoice_status','Belum Lunas');
        $notPaid = $invoices->clone()->count();
        $underDue = $invoices->clone()->whereDate('term_of_payment','>=',date('Y-m-d'))
                            ->count();
        $overDue = $invoices->clone()->whereDate('term_of_payment','<',date('Y-m-d'))
                            ->count();

        return [
            Stat::make('Belum Lunas', $notPaid)
                ->color('primary')
                ->description('Total Invoice yang belum lunas')
                ->chart([7, 2, 10, 3, 15, 4, 17]),
            Stat::make('Under Due Date', $underDue)
                ->color('success')
                ->description('Invoice yang belum jatuh tempo pembayaran')
                ->chart([7, 2, 10, 3, 15, 4, 17]),
            Stat::make('Over Due Date', $overDue)
                ->color('info')
                ->description('Sales Order yang sudah jatuh tempo pembayaran')
                ->chart([7, 2, 10, 3, 15, 4, 17]),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}
