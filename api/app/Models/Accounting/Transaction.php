<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'eden.transactions';
    protected $primaryKey = 'id';
    protected $fillable = ['date', 'from_account_id', 'to_account_id', 'note', 'amount', 'vat', 'tin', 'official_receipt'];
    public $timestamps = false;
}
