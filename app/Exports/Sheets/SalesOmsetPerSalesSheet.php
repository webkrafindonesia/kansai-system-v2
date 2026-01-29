<?php

namespace App\Exports\Sheets;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Models\SalesOrder;
use App\Models\Sales;

class SalesOmsetPerSalesSheet implements FromView
{
    protected string $yearMonth;
    protected string $salesId;

    public function __construct(int $yearMonth, string $salesId)
    {
        $this->yearMonth = $yearMonth;
        $this->salesId = $salesId;
    }

    public function view(): View
    {
        $salesOrders = SalesOrder::whereYear('invoice_date', substr($this->yearMonth, 0, 4))
            ->whereMonth('invoice_date', substr($this->yearMonth, 4, 2))
            ->whereNotNull('invoice_date')
            ->where('sales_id', $this->salesId)
            ->orderBy('invoice_no')
            ->get();

        return view('exports.salesOmset', [
            'salesOrders' => $salesOrders
        ]);
    }
}
