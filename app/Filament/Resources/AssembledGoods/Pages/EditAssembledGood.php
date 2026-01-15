<?php

namespace App\Filament\Resources\AssembledGoods\Pages;

use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use App\Filament\Resources\AssembledGoods\AssembledGoodResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAssembledGood extends EditRecord
{
    protected static string $resource = AssembledGoodResource::class;

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
