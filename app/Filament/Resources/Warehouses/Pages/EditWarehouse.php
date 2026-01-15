<?php

namespace App\Filament\Resources\Warehouses\Pages;

use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use App\Filament\Resources\Warehouses\WarehouseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Warehouse;

class EditWarehouse extends EditRecord
{
    protected static string $resource = WarehouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            // Actions\ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
