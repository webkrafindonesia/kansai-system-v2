<?php

namespace App\Filament\Resources\ReturnSalesOrders\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\ReturnSalesOrders\ReturnSalesOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReturnSalesOrders extends ListRecords
{
    protected static string $resource = ReturnSalesOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
