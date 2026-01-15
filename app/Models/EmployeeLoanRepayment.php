<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Casts\MoneyCast;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class EmployeeLoanRepayment extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $fillable = [
        'code',
        'employee_id',
        'employee_loan_id',
        'repayment_date',
        'amount',
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
        // 'amount' => MoneyCast::class,
    ];

    public function loan(){
        return $this->belongsTo(EmployeeLoan::class);
    }

    public function payroll(){
        return $this->belongsTo(EmployeePayroll::class);
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
                    'code',
                    'employee_id',
                    'employee_loan_id',
                    'repayment_date',
                    'amount',
                    'notes',
                ]);
    }
}
