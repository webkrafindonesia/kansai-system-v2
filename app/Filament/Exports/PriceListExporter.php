<?php

namespace App\Filament\Exports;

use App\Models\PriceList;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class PriceListExporter extends Exporter
{
    protected static ?string $model = PriceList::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('code')
                ->label('Kode'),
            ExportColumn::make('name')
                ->label('Nama Produk'),
            // ExportColumn::make('types')
            //     ->label('Tipe')
            //     ->formatStateUsing(fn($state)=>product_type_match($state)),
            ExportColumn::make('productCategory.name')
                ->label('Kategori'),
            ExportColumn::make('uom')
                ->label('Satuan'),
            ExportColumn::make('selling_price')
                ->label('Harga Jual')
                ->formatStateUsing(fn($state)=>$state)
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Price List telah berhasil diekspor dan ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' telah diekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' gagal diekspor.';
        }

        return $body;
    }
}
