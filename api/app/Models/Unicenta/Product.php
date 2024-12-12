<?php

namespace App\Models\Unicenta;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $connection = 'unicenta';
    protected $table = 'products';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    protected $fillable = ['name', 'pricebuy'];
    public $timestamps = false;
    public $incrementing = false;
}
