<?php

namespace App\Filament\Resources\DeliveryOrders\Pages;

use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Forms\Components\ViewField;
use App\Filament\Resources\DeliveryOrders\DeliveryOrderResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;
use App\Services\GeneratePDFDeliveryOrder;
use App\Services\MutationProcess;
use App\Models\Warehouse;
use Joaopaulolndev\FilamentPdfViewer\Infolists\Components\PdfViewerEntry;

class ViewDeliveryOrder extends ViewRecord
{
    protected static string $resource = DeliveryOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn (): bool => ! $this->record->processed_at),
            Action::make('Proses Pengiriman')
                ->color('success')
                ->icon('heroicon-o-truck')
                ->requiresConfirmation()
                ->modalHeading('Proses Pengiriman')
                ->modalDescription('Anda yakin memroses pengiriman ini? Qty di Sales Order akan disamakan dengan Qty pengiriman dan stock gudang akan berkurang.')
                ->action(function () {
                    $mutation = new MutationProcess;
                    $items = $this->record->items;
                    $origin_warehouse = Warehouse::where('types','finish_good')->first();
                    $response = $mutation->mutateItems($items, $origin_warehouse, null, 'delivery_out', null, 'Delivery Order', $this->record->id);

                    // jika response status = false
                    if(!$response['status']){
                        Notification::make()
                            ->title('Gagal')
                            ->body($response['message'])
                            ->danger()
                            ->color('danger')
                            ->send();
                    }
                    else{
                        // penyesuaian qty order di sales order
                        $salesOrder = $this->record->salesOrder;
                        foreach($this->record->items as $item){
                            $so_item = $salesOrder->items()->where('product_id', $item->product_id)->first();
                            if($so_item){
                                $so_item->qty = $item->qty;
                                $so_item->save();
                            }
                        }

                        $this->record->processed_at = now();
                        $this->record->processed_by = auth()->user()->name;
                        $this->record->save();

                        Notification::make()
                                ->title('Sukses')
                                ->body('Pengiriman telah diproses dan stock gudang telah disesuaikan. Invoice (Sales Order) siap di-download.')
                                ->success()
                                ->color('success')
                                ->send();
                    }
                })
                ->visible(fn (): bool => is_null($this->record->processed_at)),
            Action::make('Lihat Surat Jalan')
                ->label('Lihat Surat Jalan')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->modalHeading('Surat Jalan')
                ->modalSubmitAction(false) // nggak ada tombol "Submit"
                ->modalCancelActionLabel('Tutup')
                ->schema(function ($record) {
                    $salesOrder = $record->salesOrder;

                    if(is_null($salesOrder->invoice_no)){
                        $dateNumber = date('ym');
                        $dateNumberInvoice = date('ym');
                        $number = get_counter('KS-R'.$dateNumber,'KS-R'.$dateNumberInvoice,6);

                        $salesOrder->invoice_no = $number;

                        $salesOrder->invoice_date = now();
                        $salesOrder->term_of_payment = now()->addDays(60)->endOfMonth();

                        $salesOrder->save();
                    }

                    $pdf = new GeneratePDFDeliveryOrder($this->record);

                    return [
                        PdfViewerEntry::make('file')
                            ->label('Surat Jalan')
                            ->minHeight('50svh')
                            ->fileUrl($pdf->getPDF())
                            ->columnSpanFull()
                    ];
                })
                ->visible(fn (): bool => !is_null($this->record->processed_at)),
        ];
    }
}
