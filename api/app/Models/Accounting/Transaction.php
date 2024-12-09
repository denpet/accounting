<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    protected $table = 'eden.transactions';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'date', 'from_account_id', 'to_account_id', 'note', 'amount', 'vat', 'tin', 'official_receipt', 'supplier_id'];

    public function supplier(): HasOne
    {
        return $this->hasOne(Supplier::class, 'id', 'supplier_id');
    }
}
