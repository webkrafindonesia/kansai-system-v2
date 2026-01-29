<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Models\SalesOrder;

class TotalOmsetExport implements FromView
{
    protected string $yearMonth;

    public function __construct(int $yearMonth)
    {
        $this->yearMonth = $yearMonth;
    }

    public function view(): View
    {
        $salesOrders = SalesOrder::whereYear('invoice_date', substr($this->yearMonth, 0, 4))
            ->whereMonth('invoice_date', substr($this->yearMonth, 4, 2))
            ->whereNotNull('invoice_date')
            ->selectRaw('customer_id, SUM(total_omset) as omset')
            ->groupBy('customer_id')
            ->get();

        return view('exports.totalOmset', [
            'salesOrders' => $salesOrders
        ]);
    }
}
