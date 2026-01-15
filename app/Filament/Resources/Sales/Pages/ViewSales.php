<?php

namespace App\Filament\Resources\Sales\Pages;

use Filament\Actions\EditAction;
use App\Filament\Resources\Sales\SalesResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSales extends ViewRecord
{
    protected static string $resource = SalesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
