<?php

namespace App\Filament\Resources\Mutations\Pages;

use App\Filament\Resources\Mutations\MutationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMutation extends CreateRecord
{
    protected static string $resource = MutationResource::class;
}
