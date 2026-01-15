<?php

namespace App\Filament\Resources\Warehouses\Pages;

use App\Filament\Resources\Warehouses\WarehouseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Warehouse;

class CreateWarehouse extends CreateRecord
{
    protected static string $resource = WarehouseResource::class;
}
