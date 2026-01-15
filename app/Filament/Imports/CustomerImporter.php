<?php

namespace App\Filament\Imports;

use App\Models\Customer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class CustomerImporter extends Importer
{
    protected static ?string $model = Customer::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('code')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('address'),
            ImportColumn::make('city'),
            ImportColumn::make('category')
                ->rules(['max:255']),
            ImportColumn::make('phone')
                ->rules(['max:255']),
            ImportColumn::make('email')
                ->rules(['max:255']),
            ImportColumn::make('website')
                ->rules(['max:255']),
            ImportColumn::make('ktp')
                ->rules(['max:255']),
            ImportColumn::make('npwp')
                ->rules(['max:255']),
            ImportColumn::make('sales')
                ->relationship(resolveUsing: ['name']),
            ImportColumn::make('discount_sales')
                ->numeric()
                ->rules(['numeric']),
            ImportColumn::make('discount_company')
                ->numeric()
                ->rules(['numeric']),
            ImportColumn::make('expedition')
                ->rules(['max:255']),
            ImportColumn::make('notes'),
        ];
    }

    public function resolveRecord(): ?Customer
    {
        // return Customer::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Customer();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your customer import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
