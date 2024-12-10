<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\RestController;
use App\Models\Accounting\Account;

class AccountController extends RestController
{
    protected static $model = Account::class;
    protected static $validations = [
        'name' => 'required|string',
        'type' => 'required|in:A,L,E,I,C'
    ];
    protected static $indexColumns = ['id', 'name', 'type'];
    protected static $orderBy = ['type', 'name'];
    protected static $optionColumn = 'name';

    public function options()
    {
        $query = static::$model::select([
            "$this->primaryKey AS value",
            "type",
            "name",

        ]);
        $this->filter($query, request()->all());
        foreach (static::$orderBy as $orderBy) {
            $query->orderBy($orderBy);
        }
        return $query->get();
    }
}
