<?php

namespace App\Filament\Resources\DeliveryOrders\Pages;

use App\Filament\Resources\DeliveryOrders\DeliveryOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Services\AutoDeliveryItem;
use DB;

class CreateDeliveryOrder extends CreateRecord
{
    protected static string $resource = DeliveryOrderResource::class;

    public function afterCreate()
    {
        DB::beginTransaction();

        $record = $this->record;

        $doItem = new AutoDeliveryItem();
        $doItem->generateItems($record);

        DB::commit();
    }
}
