<?php

namespace App\Models\Unicenta;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $connection = 'unicenta';
    protected $table = 'categories';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    protected $fillable = ['name'];
    public $timestamps = false;
    public $incrementing = false;
}
