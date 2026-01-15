<?php

namespace App\Filament\Resources\RawMaterials\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\RawMaterials\RawMaterialResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Imports\ProductImporter;
use Filament\Actions\ImportAction;
use App\Filament\Exports\ProductExporter;
use Filament\Actions\ExportAction;

class ListRawMaterials extends ListRecords
{
    protected static string $resource = RawMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            // ImportAction::make()
            //     ->importer(ProductImporter::class)
            //     ->csvDelimiter(';'),
            // ExportAction::make()
            //     ->exporter(ProductExporter::class)
        ];
    }
}
