<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Models\SalesOrder;
use App\Exports\Sheets\SalesOmsetPerSalesSheet;

class SalesOmsetExport implements WithMultipleSheets
{
    use Exportable;

    protected string $yearMonth;

    public function __construct(int $yearMonth)
    {
        $this->yearMonth = $yearMonth;
    }

    public function sheets(): array
    {
        $sheets = [];

        $saleses = SalesOrder::whereYear('invoice_date', substr($this->yearMonth, 0, 4))
            ->whereMonth('invoice_date', substr($this->yearMonth, 4, 2))
            ->whereNotNull('invoice_date')
            ->whereNotNull('sales_id')
            ->select('sales_id')
            ->distinct()
            ->get();

        foreach ($saleses as $sales) {
            $sheets[$sales->sales->name] = new SalesOmsetPerSalesSheet($this->yearMonth, $sales->sales_id);
        }

        return $sheets;
    }

}
