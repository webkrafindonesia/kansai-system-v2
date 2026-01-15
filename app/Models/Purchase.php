<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Auth;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Stock;
use App\Models\ProductHistory;
use DB;
use Str;
use App\Casts\MoneyCast;
use App\Services\MutationProcess;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Purchase extends Model
{
    use HasUuids;
    use SoftDeletes;
    use LogsActivity;

    protected $fillable = [
        'id',
        'purchase_no',
        'deliveryorder_no',
        'date',
        'supplier_id',
        'warehouse_id',
        'notes',
        'is_accepted',
        'total_price',
        'payment_status',
        'paid_at',
        'paid_by',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'date'
    ];

    protected $casts = [
        // 'total_price' => MoneyCast::class,
        'date' => 'date'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function acceptItems()
    {
        DB::beginTransaction();
        if ($this->is_accepted) {
            return;
        }

        $mutation = new MutationProcess;
        $destination_warehouse = Warehouse::find($this->warehouse_id);
        $response = $mutation->mutateItems($this->items, null, $destination_warehouse, null, 'purchase', 'Purchase', $this->id);

        $this->is_accepted = true;
        $this->total_price = $this->items->sum('total_price');
        $this->save();
        DB::commit();
    }

    public function processPaid()
    {
        $this->payment_status = "Lunas";
        $this->paid_at = now();
        $this->paid_by = Auth::user()->id;
        $this->save();
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
                    'purchase_no',
                    'deliveryorder_no',
                    'date',
                    'supplier_id',
                    'warehouse_id',
                    'notes',
                    'is_accepted',
                    'total_price',
                    'payment_status',
                    'paid_at',
                    'paid_by',
                ]);
    }
}
