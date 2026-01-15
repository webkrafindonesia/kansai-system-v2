<?php

namespace App\Filament\Resources\Warehouses\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Warehouses\WarehouseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWarehouses extends ListRecords
{
    protected static string $resource = WarehouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
