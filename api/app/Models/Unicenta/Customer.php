<?php

namespace App\Models\Unicenta;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'unicentaopos.customers';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    protected $fillable = ['name', 'type'];
    public $timestamps = false;
    public $incrementing = false;
}
