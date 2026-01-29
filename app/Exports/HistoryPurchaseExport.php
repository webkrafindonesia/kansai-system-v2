<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Models\PurchaseItem;

class HistoryPurchaseExport implements FromView
{
    protected string $yearMonth;

    public function __construct(int $yearMonth)
    {
        $this->yearMonth = $yearMonth;
    }

    public function view(): View
    {
        $items = PurchaseItem::whereHas('purchase', function ($query) {
                $query->whereYear('date', substr($this->yearMonth, 0, 4))
                      ->whereMonth('date', substr($this->yearMonth, 4, 2))
                      ->whereNotNull('date');
            })
            ->selectRaw('
                suppliers.name,
                product_id,
                SUM(qty) as qty,
                uom,
                SUM(purchase_items.total_price) as total_price_items
            ')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->groupBy(['suppliers.name','product_id', 'uom'])
            ->get();

        return view('exports.historyPurchase', [
            'items' => $items
        ]);
    }
}
