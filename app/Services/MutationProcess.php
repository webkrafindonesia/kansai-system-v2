<?php

namespace App\Services;

use DB;
use App\Models\ProductHistory;
use App\Models\Stock;

class MutationProcess{
    public function __construct(){
        //
    }

    public function mutateItems($items, $origin_warehouse = null, $destination_warehouse = null, $origin_type = null, $destination_type = null, $reference_name = null, $reference_id = null, $total_nominal = 0){
        $response['status'] = false;
        $response['message'] = '';

        DB::beginTransaction();

        foreach($items as $item){
            // mutation out
            if(!is_null($origin_warehouse)){
                $origin_stock = Stock::where('product_id',$item->product_id)
                            ->where('warehouse_id',$origin_warehouse->id)
                            ->first();

                if(!$origin_stock instanceof Stock ||
                    $origin_stock->qty < $item->qty
                ){
                    DB::rollback();
                    $response['message'] = 'Stok '.$item->product->name.' tidak cukup. Proses dibatalkan.';
                    return $response;
                }
                else{
                    $origin_stock->qty -= $item->qty;
                    $origin_stock->save();
                }

                $history = new ProductHistory;
                $history->description = 'Mutasi dari '.$origin_warehouse->name.' (Ref: '.$reference_name.')';
                $history->product_id = $item->product_id;
                $history->qty = '-'.$item->qty;
                $history->uom = $item->uom;
                $history->types = $origin_type;
                $history->warehouse_id = $origin_warehouse->id;
                $history->reference = $reference_name;
                $history->reference_id = $reference_id;
                $history->save();
            }

            // mutation in
            if(!is_null($destination_warehouse)){
                $destination_stock = Stock::where('product_id',$item->product_id)
                            ->where('warehouse_id',$destination_warehouse->id)
                            ->first();
                if(!$destination_stock instanceof Stock){
                    $destination_stock = new Stock;
                    $destination_stock->product_id = $item->product_id;
                    $destination_stock->warehouse_id = $destination_warehouse->id;
                    $destination_stock->qty = 0;
                    $destination_stock->uom = $item->uom;
                }

                $destination_stock->qty += $item->qty;
                $destination_stock->save();

                if($reference_name == 'Purchase'){
                    // ambil harga dari purchase item
                    $total_nominal = $item->total_price;
                }
                elseif($reference_name == 'Return Sales Order'){
                    // ambil harga dari return sales order item
                    $total_nominal = $item->discounted_total_price;
                }
                elseif($reference_name == 'Assembly'){
                    // ambil harga dari proses assembly
                    $breakdowns = $item->breakdowns;
                    $nominal = 0;
                    $qty = 0;
                    $sub_nominal = 0;
                    $total_nominal = 0;
                    foreach($breakdowns as $breakdown){
                        $nominal = $breakdown->rawMaterialHistories->where('qty','>',0)->sum('total_nominal');
                        $qty = $breakdown->rawMaterialHistories->where('qty','>',0)->sum('qty');
                        $sub_nominal += $nominal / $qty;
                    }
                    if($sub_nominal > 0){
                        $total_nominal = $sub_nominal * $item->qty;
                    }
                }

                $history = new ProductHistory;
                $history->description = 'Mutasi ke '.$destination_warehouse->name.' (Ref: '.$reference_name.')';
                $history->product_id = $item->product_id;
                $history->qty = $item->qty;
                $history->uom = $item->uom;
                $history->total_nominal = $total_nominal ?? 0;
                $history->types = $destination_type;
                $history->warehouse_id = $destination_warehouse->id;
                $history->reference = $reference_name;
                $history->reference_id = $reference_id;
                $history->save();
            }
        }
        DB::commit();

        $response['status'] = true;
        $response['message'] = 'Proses berhasil dilakukan.';

        return $response;
    }

    public function mutateStockOpname($items, $warehouse = null, $reference_name = null, $reference_id = null){
        $response['status'] = false;
        $response['message'] = '';

        DB::beginTransaction();

        foreach($items as $item){
            $stock = Stock::where('product_id',$item->product_id)
                        ->where('warehouse_id',$warehouse->id)
                        ->first();

            if(!$stock instanceof Stock){
                $stock = new Stock;
                $stock->product_id = $item->product_id;
                $stock->warehouse_id = $warehouse->id;
                $stock->qty = 0;
                $stock->uom = $item->product->uom;
            }

            $stock->qty = $item->actual_qty;
            $stock->save();

            $history = new ProductHistory;
            $history->description = 'Stock Opname di '.$warehouse->name.' (Ref: '.$reference_name.')';
            $history->product_id = $item->product_id;
            $history->qty = $item->discrepancy_qty;
            $history->uom = $item->product->uom;
            $history->total_nominal = (!is_null($item->hpp)) ? $item->discrepancy_qty * $item->hpp : null;
            $history->types = 'stock_opname';
            $history->warehouse_id = $warehouse->id;
            $history->reference = $reference_name;
            $history->reference_id = $reference_id;
            $history->save();
        }

        DB::commit();

        $response['status'] = true;
        $response['message'] = 'Proses berhasil dilakukan.';

        return $response;
    }
}
