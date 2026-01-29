<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Models\ProductHistory;
use DB;

class RawMaterialUsageExport implements FromView
{
    protected string $yearMonth;

    public function __construct(int $yearMonth)
    {
        $this->yearMonth = $yearMonth;
    }

    public function view(): View
    {
        $items = ProductHistory::whereYear('created_at', substr($this->yearMonth, 0, 4))
            ->whereMonth('created_at', substr($this->yearMonth, 4, 2))
            ->whereNotNull('created_at')
            ->where('types','production_in')
            ->selectRaw('
                product_id,
                SUM(qty) as qty,
                uom
            ')
            ->addSelect(
                [
                    'avg_price' => ProductHistory::select(
                        DB::raw('SUM(total_nominal)/SUM(qty)')
                    )
                    ->whereNotNull('total_nominal')
                    ->where('total_nominal','<>',0)
                    ->whereColumn('product_id','product_histories.product_id')
                ]
            )
            ->groupBy(['product_id', 'uom'])
            ->get();

        return view('exports.rawMaterialUsage', [
            'items' => $items
        ]);
    }
}
