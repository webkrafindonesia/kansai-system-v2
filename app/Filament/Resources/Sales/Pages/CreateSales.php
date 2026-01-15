<?php

namespace App\Filament\Resources\Sales\Pages;

use App\Filament\Resources\Sales\SalesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSales extends CreateRecord
{
    protected static string $resource = SalesResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $number = get_counter('SALES-');
        $data['code'] =  $number;

        return $data;
    }
}
