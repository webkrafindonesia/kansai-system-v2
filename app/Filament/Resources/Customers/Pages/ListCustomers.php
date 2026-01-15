<?php

namespace App\Filament\Resources\Customers\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Customers\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Imports\CustomerImporter;
use App\Filament\Imports\CustomerAddressImporter;
use Filament\Actions\ImportAction;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            // ImportAction::make('import_customer')
            //     ->importer(CustomerImporter::class)
            //     ->csvDelimiter(';'),
            // ImportAction::make('import_address')
            //     ->importer(CustomerAddressImporter::class)
            //     ->csvDelimiter(';'),
        ];
    }
}
