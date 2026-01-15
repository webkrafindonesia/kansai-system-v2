<?php

namespace App\Filament\Resources\Mutations\Pages;

use Filament\Actions\EditAction;
use Filament\Actions\Action;
use App\Filament\Resources\Mutations\MutationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Models\Mutation;

class ViewMutation extends ViewRecord
{
    protected static string $resource = MutationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn (Mutation $record): bool => !$record->is_processed),
            Action::make('Process')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->visible(fn (Mutation $record): bool => !$record->is_processed)
                ->action(function (Mutation $record) {
                    $record->processMutation();
                }),
        ];
    }
}
