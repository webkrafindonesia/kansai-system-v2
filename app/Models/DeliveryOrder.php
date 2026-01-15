<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Auth;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class DeliveryOrder extends Model
{
    use HasUuids;
    use SoftDeletes;
    use LogsActivity;

    protected $fillable = [
        'id',
        'sales_order_id',
        'delivery_date',
        'warehouse_id',
        'courier_type',
        'courier_name',
        'vehicle_plate_no',
        'notes',
        'processed_at',
        'processed_by',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $dates = [
        'delivery_date',
        'processed_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'processed_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(DeliveryOrderItem::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
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
                    'sales_order_id',
                    'delivery_date',
                    'warehouse_id',
                    'courier_type',
                    'courier_name',
                    'vehicle_plate_no',
                    'notes',
                    'processed_at',
                    'processed_by',
                ]);
    }
}
