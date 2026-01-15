<?php

namespace App\Filament\Resources\Receipts\Pages;

use Filament\Actions\EditAction;
use App\Filament\Resources\Receipts\ReceiptResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewReceipt extends ViewRecord
{
    protected static string $resource = ReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
