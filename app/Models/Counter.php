<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Auth;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Counter extends Model
{
    protected $fillable = [
        'id',
        'prefix',
        'counter',
        'created_by',
        'updated_by',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

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
    }
}
