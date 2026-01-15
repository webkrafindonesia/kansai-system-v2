<?php

namespace App\Filament\Resources\Assemblies\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Assemblies\AssemblyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAssemblies extends ListRecords
{
    protected static string $resource = AssemblyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
