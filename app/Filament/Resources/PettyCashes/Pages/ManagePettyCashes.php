<?php

namespace App\Filament\Resources\PettyCashes\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\PettyCashes\Widgets\TotalPettyCash;
use App\Filament\Resources\PettyCashes\PettyCashResource;
use App\Filament\Resources\PettyCashResource\Widgets;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
// use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Pages\Concerns\ExposesTableToWidgets;

class ManagePettyCashes extends ManageRecords
{
    // use InteractsWithTable;
    use ExposesTableToWidgets;

    protected static string $resource = PettyCashResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TotalPettyCash::class,
        ];
    }
}
