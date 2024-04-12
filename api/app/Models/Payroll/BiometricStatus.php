<?php

namespace App\Models\Payroll;

use Illuminate\Database\Eloquent\Model;

class BiometricStatus extends Model
{
    protected $table = 'eden.biometric_statuses';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'name'];
    public $timestamps = false;
}
