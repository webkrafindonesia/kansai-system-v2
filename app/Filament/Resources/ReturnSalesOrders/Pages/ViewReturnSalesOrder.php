<?php

namespace App\Filament\Resources\ReturnSalesOrders\Pages;

use Filament\Actions\Action;
use App\Filament\Resources\ReturnSalesOrders\ReturnSalesOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;
use App\Services\MutationProcess;
use App\Models\Warehouse;

class ViewReturnSalesOrder extends ViewRecord
{
    protected static string $resource = ReturnSalesOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Proses Retur')
                ->color('success')
                ->icon('heroicon-o-truck')
                ->requiresConfirmation()
                ->modalHeading('Anda yakin untuk memroses retur?')
                ->modalDescription('Proses ini akan melakukan penyesuaian pada stok dan tanda terima.')
                ->action(function () {
                    $mutation = new MutationProcess;
                    $items = $this->record->items()->where('action','back_in_stock')->get();
                    $destination_warehouse = Warehouse::where('types','finish_good')->first();
                    $response = $mutation->mutateItems($items, null, $destination_warehouse, null, 'return', 'Return Sales Order', $this->record->id);

                    $this->record->processed_at = now();
                    $this->record->processed_by = auth()->user()->name;
                    $this->record->save();

                    Notification::make()
                            ->title('Sukses')
                            ->body('Retur berhasil diproses.')
                            ->success()
                            ->color('success')
                            ->send();
                })
                ->visible(fn (): bool => is_null($this->record->processed_at)),
        ];
    }
}
