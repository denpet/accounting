<?php

namespace App\Models\Payroll;

use Illuminate\Database\Eloquent\Model;

class TimeRecord extends Model
{
    protected $table = 'eden.time_records';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'employee_id', 'biometric_timestamp', 'biometric_status', 'adjusted_timestamp', 'status'];
    public $timestamps = false;
}
