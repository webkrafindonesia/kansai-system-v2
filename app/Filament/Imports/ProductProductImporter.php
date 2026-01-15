<?php

namespace App\Filament\Imports;

use App\Models\ProductProduct;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ProductProductImporter extends Importer
{
    protected static ?string $model = ProductProduct::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('product_origin')
                ->requiredMapping()
                ->rules(['required'])
                ->relationship(resolveUsing: ['code']),
            ImportColumn::make('product_reference')
                ->requiredMapping()
                ->rules(['required'])
                ->relationship(resolveUsing: ['code','name']),
            ImportColumn::make('qty')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric']),
            ImportColumn::make('uom')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
        ];
    }

    public function resolveRecord(): ?ProductProduct
    {
        // return ProductProduct::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new ProductProduct();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your product product import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
