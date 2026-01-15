<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Auth;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
{
    use HasUuids;
    use SoftDeletes;
    use LogsActivity;

    protected $fillable = [
        'id',
        'code',
        'name',
        'specification',
        'uom',
        'types',
        'product_category_id',
        'buying_price',
        'selling_price',
        'safety_stock',
        'purchasable',
        'is_active',
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
        'is_active' => 'boolean',
    ];

    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class)->withTrashed();
    }

    public function products()
    {
        return $this->hasMany(ProductProduct::class,'product_id','id');
    }

    public function histories()
    {
        return $this->hasMany(ProductHistory::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopePurchasable($query)
    {
        return $query->where('purchasable', 1);
    }

    public function scopeNonCustom($query)
    {
        return $query->where('product_category_id', '!=', 'custom');
    }

    public function getCodeAndNameAttribute()
    {
        return $this->code . ' - ' . $this->name;
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
            Cache::forget('raw_material_list');
            Cache::forget('sales_order_products');
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = Auth::user()->email;
            }
        });

        static::updated(function ($model) {
            Cache::forget('raw_material_list');
            Cache::forget('sales_order_products');
        });

        static::deleting(function ($model) {
            if (auth()->check()) {
                $model->deleted_by = Auth::user()->email;
                $model->timestamps = false; // Not triggering updated_at
                $model->saveQuietly(); // Not triggering update event
            }
        });

        static::deleted(function ($model) {
            Cache::forget('raw_material_list');
            Cache::forget('sales_order_products');
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
                    'code',
                    'name',
                    'specification',
                    'uom',
                    'types',
                    'product_category_id',
                    'buying_price',
                    'selling_price',
                    'safety_stock',
                    'purchasable',
                    'is_active',
                ]);
    }
}
