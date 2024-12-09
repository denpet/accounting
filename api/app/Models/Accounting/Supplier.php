<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'eden.suppliers';
    protected $primaryKey = 'id';
    protected $fillable = ['tin', 'name', 'address1', 'address2', 'postal_code', 'city', 'province', 'phone_number'];
    public $timestamps = false;
}
