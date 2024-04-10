<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\RestController;
use App\Models\User\Role;

class RoleController extends RestController
{
    protected static $model = Role::class;
    protected static $validations = [
        'name' => 'required|string',
    ];
    protected static $indexColumns = ['id', 'name'];
    protected static $orderBy = ['name'];
    protected static $optionColumn = 'name';
}
