<?php

namespace App\Filament\Resources\AssembledGoods\Pages;

use App\Filament\Resources\AssembledGoods\AssembledGoodResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAssembledGood extends CreateRecord
{
    protected static string $resource = AssembledGoodResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['types'] = 'assembled_good';
        $data['purchasable'] = 0;

        return $data;
    }
}
