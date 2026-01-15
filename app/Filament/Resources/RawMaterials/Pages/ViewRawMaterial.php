<?php

namespace App\Filament\Resources\RawMaterials\Pages;

use Filament\Actions\EditAction;
use App\Filament\Resources\RawMaterials\RawMaterialResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRawMaterial extends ViewRecord
{
    protected static string $resource = RawMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
