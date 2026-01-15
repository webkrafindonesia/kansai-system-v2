<?php

namespace App\Filament\Resources\AssembledGoods\Pages;

use Filament\Actions\EditAction;
use App\Filament\Resources\AssembledGoods\AssembledGoodResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAssembledGood extends ViewRecord
{
    protected static string $resource = AssembledGoodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
