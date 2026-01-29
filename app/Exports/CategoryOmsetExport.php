<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Models\SalesOrderItem;

class CategoryOmsetExport implements FromView
{
    protected string $yearMonth;

    public function __construct(int $yearMonth)
    {
        $this->yearMonth = $yearMonth;
    }

    public function view(): View
    {
        $items = SalesOrderItem::whereHas('salesOrder', function ($query) {
                $query->whereYear('invoice_date', substr($this->yearMonth, 0, 4))
                      ->whereMonth('invoice_date', substr($this->yearMonth, 4, 2))
                      ->whereNotNull('invoice_date');
            })
            ->selectRaw('
                product_categories.name,
                SUM(qty) as qty,
                SUM(
                    CASE
                        WHEN sales_orders.discount_sales > sales_orders.discount_company THEN ((100-sales_orders.discount_sales)/100) * master_total_price
                        ELSE ((100-sales_orders.discount_company)/100) * total_price
                    END
                ) as omset
            ')
            ->join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->join('products', 'sales_order_items.product_id', '=', 'products.id')
            ->join('product_categories', 'products.product_category_id', '=', 'product_categories.id')
            ->groupBy('product_categories.name')
            ->get();

        return view('exports.categoryOmset', [
            'items' => $items
        ]);
    }
}
