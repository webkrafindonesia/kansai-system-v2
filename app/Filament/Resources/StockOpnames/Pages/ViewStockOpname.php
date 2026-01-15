<?php

namespace App\Filament\Resources\StockOpnames\Pages;

use Filament\Actions\Action;
use App\Filament\Resources\StockOpnames\StockOpnameResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Colors\Color;
use App\Services\MutationProcess;

class ViewStockOpname extends ViewRecord
{
    protected static string $resource = StockOpnameResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(),
            Action::make('Lock and Adjust')
                ->label('Kunci dan Sesuaikan')
                ->color(Color::generateV3Palette('#52ad2aff'))
                ->icon('https://img.icons8.com/color/96/bill.png')
                ->requiresConfirmation()
                ->action(function ($record) {
                    $items = $record->items->where('discrepancy_qty', '!=', 0);

                    $mutate = new MutationProcess();
                    $mutate->mutateStockOpname($items, $record->warehouse, 'Stock Opname', $record->id);
                    $record->status = 'Fixed';
                    $record->save();
                })
                ->visible(fn($record)=> $record->status == 'On Check'),
            Action::make('Invalidate')
                ->label('Batalkan')
                ->color(Color::generateV3Palette('#ff0000ff'))
                ->icon('https://img.icons8.com/color/96/bill.png')
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->status = 'Invalid';
                    $record->save();
                })
                ->visible(fn($record)=>$record->status == 'On Check'),
        ];
    }
}
