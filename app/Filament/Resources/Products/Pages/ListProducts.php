<?php

namespace App\Filament\Resources\Products\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Products\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Imports\ProductImporter;
use Filament\Actions\ImportAction;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            // ImportAction::make()
            //     ->importer(ProductImporter::class)
            //     ->csvDelimiter(';')
        ];
    }
}
