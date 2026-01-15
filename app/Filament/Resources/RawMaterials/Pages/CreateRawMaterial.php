<?php

namespace App\Filament\Resources\RawMaterials\Pages;

use App\Filament\Resources\RawMaterials\RawMaterialResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Product;

class CreateRawMaterial extends CreateRecord
{
    protected static string $resource = RawMaterialResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['code'] = get_counter('RM-');
        $data['types'] = 'raw_material';

        return $data;
    }
}
