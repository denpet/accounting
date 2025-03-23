<?php

namespace App\Models\Unicenta;

use Illuminate\Database\Eloquent\Model;

class StockDiary extends Model
{
    const REASON_IN_PURCHASE = 1;
    const REASON_IN_REFUND = 2;
    const REASON_IN_MOVEMENT = 4;
    const REASON_OUT_SALE = -1;
    const REASON_OUT_REFUND = -2;
    const REASON_OUT_BREAK = -3;
    const REASON_OUT_MOVEMENT = -4;
    const REASON_OUT_SAMPLE = -5;
    const REASON_OUT_FREE = -6;
    const REASON_OUT_USED = -7;
    const REASON_OUT_SUBTRACT = -8;
    const REASON_OUT_CROSSING = 1000;
    const REASON_CYCLE_COUNT = 0;

    protected $connection = 'unicenta';
    protected $table = 'stockdiary';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    protected $fillable = ['id', 'datenew', 'reason', 'location', 'product', 'attributesetinstance_id', 'units', 'price', 'appuser', 'supplier', 'supplierdoc'];
    public $timestamps = false;
    public $incrementing = false;
}
