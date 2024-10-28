<?php

namespace App\Models\Unicenta;

use Illuminate\Database\Eloquent\Model;

class ImportedClosedCash extends Model
{
    protected $table = 'eden.imported_closed_cash';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
}
