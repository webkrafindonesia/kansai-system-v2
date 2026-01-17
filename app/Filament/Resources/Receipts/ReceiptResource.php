<?php

namespace App\Filament\Resources\Receipts;

use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DatePicker;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\ViewField;
use App\Filament\Resources\Receipts\Pages\ListReceipts;
use App\Filament\Resources\Receipts\Pages\CreateReceipt;
use App\Filament\Resources\Receipts\Pages\ViewReceipt;
use App\Filament\Resources\Receipts\Pages\EditReceipt;
use App\Filament\Resources\ReceiptResource\Pages;
use App\Filament\Resources\ReceiptResource\RelationManagers;
use App\Models\Receipt;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Services\GeneratePDFReceipt;
use Joaopaulolndev\FilamentPdfViewer\Infolists\Components\PdfViewerEntry;

class ReceiptResource extends Resource
{
    protected static ?string $model = Receipt::class;

    protected static string | \BackedEnum | null $navigationIcon = 'https://img.icons8.com/color/96/get-a-receipt.png';

    protected static string | \UnitEnum | null $navigationGroup = 'Dokumen';

    protected static ?string $label = 'Tanda Terima';

    protected static ?string $title = 'Tanda Terima';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('receipt_date')
                    ->label('Tanggal Receipt')
                    ->date('d F Y'),
                TextColumn::make('items_count')
                    ->label('Jumlah Faktur')
                    ->counts('items'),
                TextColumn::make('items')
                    ->formatStateUsing(function($record){
                        $display = '';
                        foreach($record->items as $item){
                            if($item->reference == 'Sales Order')
                                $display .= '- INV: '.$item->salesOrder->invoice_no.'<br/>';
                            else
                                $display .= '- '.$item->reference.'<br/>';
                        }
                        return $display;
                    })
                    ->html()
            ])
            ->recordUrl(fn () => null)
            ->groups([
                Group::make('customer.name')
                    ->label('Customer')
                    ->collapsible()
            ])
            ->filters([
                // Tables\Filters\TrashedFilter::make(),
                Filter::make('invoice_date_filter')
                    ->schema([
                        DatePicker::make('startDate')
                            ->default(now()->startOfMonth())
                            ->native(false)
                            ->label('Tanggal Awal'),
                        DatePicker::make('endDate')
                            ->default(now())
                            ->native(false)
                            ->label('Tanggal Akhir'),
                    ])
                    ->query(function ( $query, $data){
                        return $query
                            ->when($data['startDate'] ?? null, fn ($q, $date) =>
                                $q->whereDate('receipt_date', '>=', $date)
                            )
                            ->when($data['endDate'] ?? null, fn ($q, $date) =>
                                $q->whereDate('receipt_date', '<=', $date)
                            );
                    })
                    ->indicateUsing(function ($data) {
                        $indicators = [];

                        if ($data['startDate'] ?? null) {
                            $indicators['startDate'] = 'Tanda Terima dari ' . Carbon::parse($data['startDate'])->toFormattedDateString();
                        }
                        if ($data['endDate'] ?? null) {
                            $indicators['endDate'] = 'Tanda Terima hingga ' . Carbon::parse($data['endDate'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->recordActions([
                Action::make('view_receipt')
                    ->label('Lihat Tanda Terima')
                    ->modalHeading('Tanda Terima')
                    ->modalSubmitAction(false) // nggak ada tombol "Submit"
                    ->modalCancelActionLabel('Tutup')
                    ->schema(function($record){
                        $pdf = new GeneratePDFReceipt();

                        return [
                            PdfViewerEntry::make('file')
                                    ->label('Tanda Terima')
                                    ->minHeight('50svh')
                                    ->fileUrl($pdf->getPDF($record))
                                    ->columnSpanFull()
                        ];
                    }),
            ])
            ->toolbarActions([

            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReceipts::route('/'),
            'create' => CreateReceipt::route('/create'),
            'view' => ViewReceipt::route('/{record}'),
            'edit' => EditReceipt::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
