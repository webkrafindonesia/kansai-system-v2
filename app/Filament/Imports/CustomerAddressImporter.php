<?php

namespace App\Filament\Imports;

use App\Models\CustomerAddress;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class CustomerAddressImporter extends Importer
{
    protected static ?string $model = CustomerAddress::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('customer')
                ->requiredMapping()
                ->relationship(resolveUsing: ['code','name']),
            ImportColumn::make('title')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('address'),
            ImportColumn::make('city'),
            ImportColumn::make('notes'),
        ];
    }

    public function resolveRecord(): ?CustomerAddress
    {
        // return CustomerAddress::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new CustomerAddress();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your customer address import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
