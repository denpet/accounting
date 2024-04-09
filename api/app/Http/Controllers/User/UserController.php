<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\RestController;
use App\Models\User\User;

class UserController extends RestController
{
    protected static $model = User::class;
    protected static $validations = [
        'name' => 'required|string',
        'email' => 'required|email',
        'password' => 'nullable|string'
    ];
    protected static $indexColumns = ['id', 'name', 'email'];
    protected static $orderBy = ['name', 'email'];
    protected static $optionColumn = 'email';
}
