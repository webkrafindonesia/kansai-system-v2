<?php

namespace App\Services;

use DB;

class AutoDeliveryItem{
    public function __construct(){
        //
    }

    public function generateItems($deliveryOrder){
        DB::beginTransaction();

        $salesOrder = $deliveryOrder->salesOrder;
        foreach ($salesOrder->items as $soItem) {
            $deliveryOrder->items()->create([
                'delivery_order_id' => $deliveryOrder->id,
                'product_id' => $soItem->product_id,
                'qty' => $soItem->qty,
                'uom' => $soItem->uom,
                'created_by' => auth()->user()->name,
                'updated_by' => auth()->user()->name,
            ]);
        }

        DB::commit();
    }
}
