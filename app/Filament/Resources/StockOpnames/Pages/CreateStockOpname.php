<?php

namespace App\Filament\Resources\StockOpnames\Pages;

use App\Filament\Resources\StockOpnames\StockOpnameResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\StockOpnameItem;
use App\Models\Product;
use App\Models\Stock;

class CreateStockOpname extends CreateRecord
{
    protected static string $resource = StockOpnameResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] =  'On Check';

        return $data;
    }

    protected function afterCreate()
    {
        $record = $this->record;

        if($record->options == 'raw_material'){
            $items = Product::where('types','raw_material')->get();
        }
        elseif($record->options == 'finished_assembled'){
            $items = Product::whereIn('types',['finish_good','assembled_good'])->get();
        }
        elseif($record->options == 'all'){
            $items = Product::get();
        }

        if($record->options != 'manual'){
            foreach($items as $item){
                $stock = Stock::where('product_id', $item->id)
                    ->where('warehouse_id', $record->warehouse_id)
                    ->first();

                $items = new StockOpnameItem;
                $items->stock_opname_id = $record->id;
                $items->product_id = $item->id;
                $items->system_qty = $stock ? $stock->qty : 0;
                $items->actual_qty = 0;
                $items->discrepancy_qty = $stock ? $stock->qty : 0;
                $items->hpp = null;
                $items->notes = null;
                $items->save();
            }
        }
    }
}
