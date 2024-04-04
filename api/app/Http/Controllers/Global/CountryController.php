<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\RestController;
use App\Models\Global\Country;

class CountryController extends RestController
{
    protected static $model = Country::class;
    protected static $validations = [
        'code' => 'required|string',
        'name' => 'required|string',
    ];
    protected static $indexColumns = ['id', 'code', 'name'];
    protected static $orderBy = ['name'];
    protected static $optionColumn = 'name';
}
