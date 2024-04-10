<?php

namespace App\Models\Unicenta;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $connection = 'unicenta';
    protected $table = 'customers';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    protected $fillable = ['name', 'type'];
    public $timestamps = false;
    public $incrementing = false;
}
