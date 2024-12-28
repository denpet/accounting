<?php

namespace App\Models\Unicenta;

use Illuminate\Database\Eloquent\Model;

class StockCurrent extends Model
{
    protected $connection = 'unicenta';
    protected $table = 'stockcurrent';
    protected $primaryKey = 'product';
    protected $keyType = 'string';
    protected $fillable = ['location', 'product', 'attributesetinstance_id', 'units'];
    public $timestamps = false;
    public $incrementing = false;
}
