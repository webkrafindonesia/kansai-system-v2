<?php

namespace App\Services;

use DB;
use App\Models\SalesOrder;
use App\Models\Assembly;
use App\Jobs\AssemblyProcess;

class AutoAssemblyItem{
    public function __construct(){
        //
    }

    public function generateItems($salesOrder, $user, $auto_process = false){
        $response['status'] = 'false';
        $response['message'] = '';

        if($salesOrder->breakdowns->count() == 0){
            return;
        }

        DB::beginTransaction();

        $assembly_date = now();
        $assembly = new Assembly;
        $assembly->code = get_counter('ASM-'.$assembly_date->format('Y'),'ASM-'.$assembly_date->format('Ymd'));
        $assembly->assembly_date = $assembly_date;
        $assembly->status = 'Draft';
        $assembly->sales_order_id = $salesOrder->id;
        $assembly->notes = 'Auto generated dari Sales Order '.$salesOrder->salesorder_no;
        $assembly->created_by = $user->name;
        $assembly->updated_by = $user->name;
        $assembly->save();

        $count = 0;
        foreach ($salesOrder->items()->whereNull('assembly_id')->whereHas('breakdowns')->get() as $soItem) {
            $stockQty = $soItem->stocks()->sum('qty') ?? 0;
            if($stockQty >= $soItem->qty){
                continue;
            }
            $assemblyItem = $assembly->items()->create([
                'assembly_id' => $assembly->id,
                'product_id' => $soItem->product_id,
                'qty' => $soItem->qty - $stockQty,
                'uom' => $soItem->uom,
                'created_by' => $user->name,
                'updated_by' => $user->name,
            ]);

            $assembly->breakdowns()->createMany(
                $soItem->breakdowns->map(function($breakdown) use ($soItem, $assembly, $assemblyItem, $user){
                    return [
                        'assembly_id' => $assembly->id,
                        'assembly_item_id' => $assemblyItem->id,
                        'product_id' => $breakdown->product_id,
                        'qty' => $breakdown->qty,
                        'uom' => $breakdown->uom,
                        'created_by' => $user->name,
                        'updated_by' => $user->name,
                    ];
                })->toArray()
            );

            $soItem->assembly_id = $assembly->id;
            $soItem->save();

            $count++;
        }

        if($count == 0){
            // tidak ada item rakit yang dibuat
            DB::rollback();

            $response['message'] = 'Tidak ada item rakit yang dibuat.';
            return $response;
        }

        DB::commit();

        if($auto_process){
            // dispatch job proses perakitan
            AssemblyProcess::dispatch($assembly, $user);
        }

        $response['status'] = 'true';
        $response['message'] = 'Draft Perakitan '.$assembly->code.' berhasil dibuat.';

        return $response;
    }
}
