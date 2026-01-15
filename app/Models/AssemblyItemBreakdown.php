<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class AssemblyItemBreakdown extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $fillable = [
        'assembly_id',
        'assembly_item_id',
        'product_id',
        'qty',
        'uom',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function assembly()
    {
        return $this->belongsTo(Assembly::class);
    }

    public function assemblyItem()
    {
        return $this->belongsTo(AssemblyItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function rawMaterialHistories()
    {
        return $this->hasMany(ProductHistory::class, 'product_id','product_id');
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
                    'assembly_id',
                    'assembly_item_id',
                    'product_id',
                    'qty',
                    'uom',
                ]);
    }
}
