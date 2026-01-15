<?php

namespace App\Filament\Resources\SalesOrders\Pages;

use App\Filament\Resources\SalesOrders\SalesOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\SalesOrder;
use Carbon\Carbon;

class CreateSalesOrder extends CreateRecord
{
    protected static string $resource = SalesOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $date = Carbon::parse($data['date']);
        $data['salesorder_no'] = get_counter('SO-'.$date->format('Y'),'SO-'.$date->format('Ymd'));

        return $data;
    }
}
