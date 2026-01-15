<?php

namespace App\Filament\Resources\Assemblies\Pages;

use Filament\Actions\EditAction;
use App\Filament\Resources\Assemblies\AssemblyResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAssembly extends ViewRecord
{
    protected static string $resource = AssemblyResource::class;

    protected $listeners = ['refresh' => '$refresh'];

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
