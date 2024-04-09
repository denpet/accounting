<?php

namespace App\Models\Global;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'eden.countries';
    protected $primaryKey = 'id';
    protected $fillable = ['code', 'name'];
    public $timestamps = false;
}
