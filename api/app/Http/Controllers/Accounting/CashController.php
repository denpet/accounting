<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\RestController;
use App\Models\Accounting\Cash;

class CashController extends RestController
{
    protected static $model = Cash::class;
    protected static $validations = [
        'date' => 'required|date',
        'amount' => 'required|numeric',
        'safe' => 'required|numeric',
    ];
    protected static $indexColumns = ['id', 'date', 'amount', 'safe'];
    protected static $orderBy = ['datetime'];
}
