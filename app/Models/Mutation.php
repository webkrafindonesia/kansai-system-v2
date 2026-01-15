<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Auth;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use App\Models\Stock;
use App\Models\ProductHistory;
use Str;
use Filament\Notifications\Notification;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Mutation extends Model
{
    use HasUuids;
    use SoftDeletes;
    use LogsActivity;

    protected $fillable = [
        'id',
        'date',
        'origin',
        'destination',
        'reference',
        'is_processed',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $dates = [
        'date',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'date' => 'date'
    ];

    public function items()
    {
        return $this->hasMany(MutationItem::class);
    }

    public function origin_warehouse()
    {
        return $this->belongsTo(Warehouse::class,'origin','code');
    }

    public function destination_warehouse()
    {
        return $this->belongsTo(Warehouse::class,'destination','code');
    }

    public function processMutation()
    {
        if ($this->is_processed) {
            return;
        }

        DB::beginTransaction();
        foreach ($this->items as $item) {
            $product = $item->product;

            // Kurangi stok di gudang asal
            $origin = Warehouse::where('code',$this->origin)->first();
            $originStock = Stock::firstOrCreate(
                ['product_id' => $product->id, 'uom' => $product->uom, 'warehouse_id' => $origin->id],
                ['qty' => 0]
            );
            if ($originStock->qty < $item->qty) {
                DB::rollBack();
                Notification::make()
                    ->title('Gagal Memproses Mutasi')
                    ->body('Stok tidak mencukupi di gudang asal untuk produk ['.$product->code.'] '.$product->name.'.')
                    ->danger()
                    ->color('danger')
                    ->send();
                return;
            }
            $originStock->qty -= $item->qty;
            $originStock->save();

            // Product History - Origin
            ProductHistory::create([
                'id' => Str::uuid(),
                'description' => 'Mutation from ' . $this->origin . ' (Ref: ' . $this->reference . ')',
                'product_id' => $product->id,
                'qty' => -$item->qty,
                'uom' => $item->uom,
                'types' => 'mutation_out',
                'warehouse_id' => $origin->id,
                'reference_id' => $this->id,
                'created_by' => Auth::user()->email,
                'updated_by' => Auth::user()->email,
            ]);

            // Tambah stok di gudang tujuan
            $destination = Warehouse::where('code',$this->destination)->first();
            $destinationStock = Stock::firstOrCreate(
                ['product_id' => $product->id, 'uom' => $product->uom, 'warehouse_id' => $destination->id],
                ['qty' => 0]
            );
            $destinationStock->qty += $item->qty;
            $destinationStock->save();

            // Product History - Destination
            ProductHistory::create([
                'id' => Str::uuid(),
                'description' => 'Mutation to ' . $this->destination . ' (Ref: ' . $this->reference . ')',
                'product_id' => $product->id,
                'qty' => $item->qty,
                'uom' => $item->uom,
                'types' => 'mutation_in',
                'warehouse_id' => $destination->id,
                'reference_id' => $this->id,
                'created_by' => Auth::user()->email,
                'updated_by' => Auth::user()->email,
            ]);
        }

        $this->is_processed = true;
        $this->save();
        DB::commit();

        Notification::make()
            ->title('Mutasi Berhasil Diproses')
            ->body('Mutasi dengan ID ' . $this->id . ' telah berhasil diproses.')
            ->success()
            ->color('success')
            ->send();
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = Auth::user()->email;
                $model->updated_by = Auth::user()->email;
            }
        });

        static::created(function ($model) {
            //
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = Auth::user()->email;
            }
        });

        static::updated(function ($model) {
            //
        });

        static::deleting(function ($model) {
            if (auth()->check()) {
                $model->deleted_by = Auth::user()->email;
                $model->timestamps = false; // Not triggering updated_at
                $model->saveQuietly(); // Not triggering update event
            }
        });

        static::deleted(function ($model) {
            //
        });

        static::restoring(function ($model) {
            if (auth()->check()) {
                $model->deleted_by = null;
                $model->updated_by =  Auth::user()->email;
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
                ->logOnly([
                    'date',
                    'origin',
                    'destination',
                    'reference',
                    'is_processed',
                    'created_by',
                    'updated_by',
                    'deleted_by',
                ]);
    }
}
