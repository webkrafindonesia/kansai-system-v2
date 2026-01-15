<?php

namespace App\Filament\Resources\Purchases\Pages;

use Filament\Actions\EditAction;
use Filament\Actions\Action;
use App\Filament\Resources\Purchases\PurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;

class ViewPurchase extends ViewRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('Pelunasan')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalHeading('Pelunasan')
                ->modalDescription('Anda yakin menandai Pembelian ini sebagai Lunas?')
                ->action(function ($record) {
                    $record->processPaid();

                    Notification::make()
                        ->title('Success')
                        ->body('Pembelian berhasil ditandai sebagai Lunas.')
                        ->success()
                        ->color('success')
                        ->send();
                })
                ->visible(fn($record)=>$record->is_accepted && $record->payment_status=='Belum Lunas'),
        ];
    }
}
