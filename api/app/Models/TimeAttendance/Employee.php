<?php

namespace App\Models\TimeAttendance;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'eden.employees';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'name', 'rate', 'active'];
    public $timestamps = false;
}
