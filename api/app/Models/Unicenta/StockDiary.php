<?php

namespace App\Models\Unicenta;

use Illuminate\Database\Eloquent\Model;

class StockDiary extends Model
{
    protected $connection = 'unicenta';
    protected $table = 'stockdiary';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    protected $fillable = ['id', 'datenew', 'reason', 'location', 'product', 'attributesetinstance_id', 'units', 'price', 'appuser', 'supplier', 'supplierdoc'];
    public $timestamps = false;
    public $incrementing = false;
}
