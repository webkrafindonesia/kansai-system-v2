<?php

namespace App\Jobs;

use Filament\Actions\Action;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Assembly;
use App\Models\Warehouse;
use App\Services\MutationProcess;
use Filament\Notifications\Notification;
use App\Filament\Resources\AssemblyResource;

class AssemblyProcess implements ShouldQueue
{
    use Queueable;

    public $assembly;
    public $recipient;
    /**
     * Create a new job instance.
     */
    public function __construct(Assembly $assembly, $recipient)
    {
        $this->assembly = $assembly;
        $this->recipient = $recipient;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $mutation = new MutationProcess;

        // potong stock raw material sesuai breakdown dan qty produk rakit
        $breakdowns = $this->assembly->breakdowns()
                    ->select('assembly_item_breakdowns.product_id','assembly_item_breakdowns.uom')
                    ->join('assembly_items','assembly_item_breakdowns.assembly_item_id','assembly_items.id')
                    ->selectRaw('SUM(assembly_item_breakdowns.qty * assembly_items.qty) as qty')
                    ->groupBy('assembly_item_breakdowns.product_id','assembly_item_breakdowns.uom')
                    ->get();
        $origin_warehouse = Warehouse::where('types','raw_material')->first();
        $response = $mutation->mutateItems($breakdowns, $origin_warehouse, null, 'production_in', null, 'Assembly', $this->assembly->id);
        if(!$response['status']){
            $this->assembly->status = 'Draft';
            $this->assembly->save();

            Notification::make()
                ->title('Proses Perakitan Gagal ['.$this->assembly->code.']')
                ->body($response['message'])
                ->danger()
                ->color('danger')
                ->actions([
                    Action::make('view')
                        ->label('Lihat')
                        ->button()
                        ->color('primary')
                        ->url(AssemblyResource::getUrl('view', ['record' => $this->assembly->id]))
                ])
                ->sendToDatabase($this->recipient);

            return;
        }

        // tambahkan stock barang rakit ke gudang barang jadi
        $items = $this->assembly->items;
        $destination_warehouse = Warehouse::where('types','finish_good')->first();
        $response = $mutation->mutateItems($items, null, $destination_warehouse, null, 'production_out', 'Assembly', $this->assembly->id);

        $this->assembly->status = 'Done';
        $this->assembly->processed_at = now();
        $this->assembly->save();

        Notification::make()
            ->title('Proses Perakitan Sukses ['.$this->assembly->code.']')
            ->body($response['message'])
            ->success()
            ->color('success')
            ->actions([
                Action::make('view')
                    ->label('Lihat')
                    ->button()
                    ->color('primary')
                    ->url(AssemblyResource::getUrl('view', ['record' => $this->assembly->id]))
            ])
            ->sendToDatabase($this->recipient);
    }
}
