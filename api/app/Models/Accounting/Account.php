<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $table = 'eden.accounts';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'type'];
    public $timestamps = false;
}
