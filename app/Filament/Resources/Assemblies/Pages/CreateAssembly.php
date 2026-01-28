<?php

namespace App\Filament\Resources\Assemblies\Pages;

use App\Filament\Resources\Assemblies\AssemblyResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Carbon\Carbon;

class CreateAssembly extends CreateRecord
{
    protected static string $resource = AssemblyResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $date = Carbon::parse($data['assembly_date']);
        $data['code'] = get_counter('ASM-'.$date->format('Ym'),'ASM-'.$date->format('Ymd'));

        return $data;
    }
}
