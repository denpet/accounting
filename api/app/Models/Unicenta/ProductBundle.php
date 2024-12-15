<?php

namespace App\Models\Unicenta;

use Illuminate\Database\Eloquent\Model;

class ProductBundle extends Model
{
    protected $connection = 'unicenta';
    protected $table = 'products_bundle';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    protected $fillable = ['id', 'name', 'product', 'product_bundle', 'quantity'];
    public $timestamps = false;
    public $incrementing = false;
}
