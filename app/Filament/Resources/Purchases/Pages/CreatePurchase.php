<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Filament\Resources\Purchases\PurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Carbon\Carbon;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $date = Carbon::parse($data['date']);
        $data['purchase_no'] = get_counter('PURC-'.$date->format('Y'),'PURC-'.$date->format('Ymd'));

        return $data;
    }
}
