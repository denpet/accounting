<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\RestController;
use App\Models\Accounting\Transaction;

class TransactionController extends RestController
{
    protected static $model = Transaction::class;
    protected static $validations = [
        'date' => 'required|date',
        'from_account_id' => 'required|exists:eden.accounts',
        'to_account_id' => 'required|exists:eden.accounts',
        'note' => 'required|string',
        'amount' => 'required|numeric',
        'vat' => 'required|numeric',
        'tin' => 'nullable|string',
        'official_receipt' => 'nullable|string',
    ];
    protected static $indexColumns = ['id', 'date', 'note'];
    protected static $orderBy = ['date', 'id'];
}
