<?php

namespace App\Models\Payroll;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TimeRecord extends Model
{
    protected $table = 'eden.time_records';
    protected $primaryKey = 'id';
    protected $fillable = ['zk_id', 'employee_id', 'biometric_timestamp', 'biometric_status', 'adjusted_timestamp', 'hide'];
    public $timestamps = false;

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function biometricStatus(): HasOne
    {
        return $this->hasOne(BiometricStatus::class, 'id', 'biometric_status');
    }
}
