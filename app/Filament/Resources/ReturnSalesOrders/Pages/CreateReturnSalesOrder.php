<?php

namespace App\Filament\Resources\ReturnSalesOrders\Pages;

use App\Filament\Resources\ReturnSalesOrders\ReturnSalesOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\SalesOrder;

class CreateReturnSalesOrder extends CreateRecord
{
    protected static string $resource = ReturnSalesOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $salesOrder = SalesOrder::find($data['sales_order_id']);
        $data['customer_id'] = $salesOrder->customer_id;

        return $data;
    }
}
