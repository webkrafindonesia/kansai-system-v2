<?php

namespace App\Filament\Resources\Sales\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Sales\SalesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSales extends ListRecords
{
    protected static string $resource = SalesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
