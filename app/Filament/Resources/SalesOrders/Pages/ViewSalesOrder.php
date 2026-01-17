<?php

namespace App\Filament\Resources\SalesOrders\Pages;

use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Forms\Components\ViewField;
use App\Filament\Resources\SalesOrders\SalesOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\MaxWidth;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use App\Services\GeneratePDFInvoiceSales;
use App\Services\GeneratePDFInvoiceCustomer;
use Joaopaulolndev\FilamentPdfViewer\Infolists\Components\PdfViewerEntry;

class ViewSalesOrder extends ViewRecord
{
    protected static string $resource = SalesOrderResource::class;

    protected $listeners = ['refresh' => '$refresh'];

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn($record) => is_null($record->delivery_order_id)),
            // Actions\Action::make('Kunci dan Generate PO')
            //     ->label('Kunci dan Generate PO')
            //     ->color('success')
            //     ->icon('https://img.icons8.com/color/96/expeditedssl.png')
            //     ->modalWidth(MaxWidth::Small)
            //     ->modalSubmitActionLabel('Submit') // Customize modal submit button label
            //     ->modalHeading('Input Nomor PO') // Customize modal heading
            //     ->modalDescription('Masukkan nomor PO Customer.') // Add a description
            //     ->form([
            //         TextInput::make('purchaseorder_no')
            //             ->label('Nomor PO')
            //             ->unique(ignoreRecord: true)
            //             ->required(),
            //     ])
            //     ->action(function (array $data, $record) {
            //         $record->purchaseorder_no = $data['purchaseorder_no'];
            //         $record->save();

            //         $check = $record->breakdowns->count();

            //         if($check == 0){ // artinya barang jadi semua
            //             $record->production_start = 1;
            //             $record->production_end = 1;
            //             $record->save();
            //         }

            //         Notification::make()
            //             ->title('Success')
            //             ->body('Nomor PO berhasil disimpan.')
            //             ->success()
            //             ->color('success')
            //             ->send();

            //         $this->js('window.location.reload()');
            //     })
            //     ->visible(fn($record)=>is_null($record->purchaseorder_no) && $record->items->count()),
            Action::make('Invoice Sales')
                ->label('Invoice Sales')
                ->color(Color::generateV3Palette('#ff7272ff'))
                ->icon('https://img.icons8.com/color/96/bill.png')
                ->modalHeading('Invoice Sales')
                ->modalSubmitAction(false) // nggak ada tombol "Submit"
                ->modalCancelActionLabel('Tutup')
                ->schema(function($record){
                    $pdf = new GeneratePDFInvoiceSales($record);

                    return [
                        PdfViewerEntry::make('file')
                            ->label('Invoice Sales')
                            ->minHeight('50svh')
                            ->fileUrl($pdf->getPDF())
                            ->columnSpanFull()
                    ];
                })
                ->visible(fn($record)=>$record->deliveryOrder && !is_null($record->deliveryOrder->processed_at)),
            Action::make('Invoice Customer')
                ->label('Invoice Customer')
                ->color(Color::generateV3Palette('#b829ffff'))
                ->icon('https://img.icons8.com/color/96/bill.png')
                ->modalHeading('Invoice Customer')
                ->modalSubmitAction(false) // nggak ada tombol "Submit"
                ->modalCancelActionLabel('Tutup')
                ->schema(function($record){
                    $pdf = new GeneratePDFInvoiceCustomer($record);

                    return [
                        PdfViewerEntry::make('file')
                            ->label('Invoice Customer')
                            ->minHeight('50svh')
                            ->fileUrl($pdf->getPDF())
                            ->columnSpanFull()
                    ];
                })
                ->visible(fn($record)=>$record->deliveryOrder && !is_null($record->deliveryOrder->processed_at)),
        ];
    }
}
