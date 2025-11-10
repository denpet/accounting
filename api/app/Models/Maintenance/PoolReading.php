<?php

namespace App\Models\Maintenance;

use Illuminate\Database\Eloquent\Model;

class PoolReading extends Model
{
    protected $table = 'eden.pool_readings';
    protected $primaryKey = 'id';
    protected $fillable = ['date', 'free_chlorine', 'ph', 'alkalinity', 'total_chlorine', 'hardness', 'cyanuric_acid'];
    public $timestamps = true;
}
