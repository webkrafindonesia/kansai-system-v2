<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Casts\MoneyCast;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Casts\Attribute;

class SalesOrderItem extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $fillable = [
        'sales_order_id',
        'product_id',
        'qty',
        'uom',
        'price',
        'total_price',
        'master_price',
        'master_total_price',
        'notes',
        'assembly_id',
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
        // 'price' => MoneyCast::class,
        // 'total_price' => MoneyCast::class,
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function breakdowns()
    {
        return $this->hasMany(SalesOrderItemBreakdown::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class,'product_id','product_id');
    }

    public function assembly()
    {
        return $this->belongsTo(Assembly::class);
    }

    // protected function getDiscountedTotalPriceAttribute(): Attribute
    // {
    //     return Attribute::get(function () {
    //         $order = $this->salesOrder;

    //         if (!$order) {
    //             return null; // jika relasi belum dimuat
    //         }

    //         $discSales = $order->discount_sales;
    //         $discCompany = $order->discount_company;

    //         if ($discSales > $discCompany) {
    //             return ((100 - $discSales) / 100) * $this->master_total_price;
    //         }

    //         return ((100 - $discCompany) / 100) * $this->total_price;
    //     });
    // }

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
                    'product_id',
                    'qty',
                    'uom',
                    'price',
                    'total_price',
                    'master_price',
                    'master_total_price',
                    'notes',
                ]);
    }
}
