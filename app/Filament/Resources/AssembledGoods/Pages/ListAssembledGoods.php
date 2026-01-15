<?php

namespace App\Filament\Resources\AssembledGoods\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\AssembledGoods\AssembledGoodResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Imports\ProductImporter;
use App\Filament\Imports\ProductProductImporter;
use Filament\Actions\ImportAction;
use App\Filament\Exports\ProductExporter;
use Filament\Actions\ExportAction;

class ListAssembledGoods extends ListRecords
{
    protected static string $resource = AssembledGoodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            // ImportAction::make('products')
            //     ->importer(ProductImporter::class)
            //     ->csvDelimiter(';'),
            // ImportAction::make('product breakdowns')
            //     ->importer(ProductProductImporter::class)
            //     ->csvDelimiter(';'),
            // ExportAction::make()
            //     ->exporter(ProductExporter::class)
        ];
    }
}
