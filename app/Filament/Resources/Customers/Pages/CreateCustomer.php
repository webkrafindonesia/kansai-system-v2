<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Customer;
use App\Models\CustomerAddress;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $number = get_counter('CUST-');
        $data['code'] =  $number;

        return $data;
    }

    protected function afterCreate()
    {
        $record = $this->record;

        $customerAddress = new CustomerAddress;
        $customerAddress->customer_id = $record->id;
        $customerAddress->title = "PUSAT";
        $customerAddress->address = $record->address;
        $customerAddress->city = $record->city;
        $customerAddress->save();
    }
}
