<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ReturnSalesOrderItem extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $fillable = [
        'id',
        'return_sales_order_id',
        'sales_order_item_id',
        'product_id',
        'qty',
        'uom',
        'action',
        'price',
        'total_price',
        'discounted_total_price',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'return_date' => 'date',
    ];

    public function returnSalesOrderItem()
    {
        return $this->belongsTo(ReturnSalesOrderItem::class);
    }

    public function salesOrderItem()
    {
        return $this->belongsTo(SalesOrderItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
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
                    'return_sales_order_id',
                    'sales_order_item_id',
                    'product_id',
                    'qty',
                    'uom',
                    'action',
                    'price',
                    'total_price',
                    'discounted_total_price',
                    'notes',
                ]);
    }
}
