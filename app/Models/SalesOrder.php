<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Auth;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Casts\MoneyCast;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class SalesOrder extends Model
{
    use HasUuids;
    use SoftDeletes;
    use LogsActivity;

    protected $fillable = [
        'id',
        'salesorder_no',
        'purchaseorder_no',
        'date',
        'customer_id',
        'sales_id',
        'discount_sales',
        'discount_company',
        'notes',
        'production_start',
        'production_end',
        'delivery_order_id',
        'invoice_no',
        'invoice_date',
        'invoice_status',
        'term_of_payment',
        'total_omset',
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
    ];

    protected $casts = [
        'production_start' => 'boolean',
        'production_end' => 'boolean',
        'invoice_date' => 'date',
        'term_of_payment' => 'date',
        'paid_at' => 'datetime',
        // 'total_omset' => MoneyCast::class
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function sales()
    {
        return $this->belongsTo(Sales::class);
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function breakdowns()
    {
        return $this->hasMany(SalesOrderItemBreakdown::class);
    }

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class);
    }

    public function assembly()
    {
        return $this->hasOne(Assembly::class);
    }

    public function getSalesCommissionAttribute()
    {
        return $this->items->sum('total_price')*((100-$this->discount_company)-(100-$this->discount_sales))/100;
    }

    public function processPaid()
    {
        $this->invoice_status = "Lunas";
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
                    'salesorder_no',
                    'purchaseorder_no',
                    'date',
                    'customer_id',
                    'sales_id',
                    'discount_sales',
                    'discount_company',
                    'notes',
                    'production_start',
                    'production_end',
                    'delivery_order_id',
                    'invoice_no',
                    'invoice_date',
                    'invoice_status',
                    'term_of_payment',
                    'total_omset',
                    'paid_at',
                    'paid_by',
                ]);
    }
}
