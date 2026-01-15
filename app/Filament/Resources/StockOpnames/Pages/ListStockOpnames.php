<?php

namespace App\Filament\Resources\StockOpnames\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\StockOpnames\StockOpnameResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStockOpnames extends ListRecords
{
    protected static string $resource = StockOpnameResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
