<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Models\DeliveryOrder;

class HistoryDeliveryOrderExport implements FromView
{
    protected string $yearMonth;

    public function __construct(int $yearMonth)
    {
        $this->yearMonth = $yearMonth;
    }

    public function view(): View
    {
        $deliveryOrders = DeliveryOrder::whereYear('delivery_date', substr($this->yearMonth, 0, 4))
            ->whereMonth('delivery_date', substr($this->yearMonth, 4, 2))
            ->whereNotNull('processed_at')
            ->get();

        return view('exports.historyDeliveryOrder', [
            'deliveryOrders' => $deliveryOrders
        ]);
    }
}
