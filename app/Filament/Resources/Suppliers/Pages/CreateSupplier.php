<?php

namespace App\Filament\Resources\Suppliers\Pages;

use App\Filament\Resources\Suppliers\SupplierResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Supplier;
use App\Models\SupplierAddress;

class CreateSupplier extends CreateRecord
{
    protected static string $resource = SupplierResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $number = get_counter('SUPP-');
        $data['code'] =  $number;

        return $data;
    }

    protected function afterCreate()
    {
        $record = $this->record;

        $supplierAddress = new SupplierAddress;
        $supplierAddress->supplier_id = $record->id;
        $supplierAddress->title = "PUSAT";
        $supplierAddress->address = $record->address;
        $supplierAddress->city = $record->city;
        $supplierAddress->save();
    }
}
