<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class Cash extends Model
{
    protected $table = 'eden.cash';
    protected $primaryKey = 'id';
    protected $fillable = ['date', 'amount', 'safe'];
    public $timestamps = false;
}
