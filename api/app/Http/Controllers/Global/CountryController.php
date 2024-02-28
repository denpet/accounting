<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\RestController;
use App\Models\Global\Country;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;

class CountryController extends RestController
{
    protected static $model = Country::class;
    protected static $validations = [
        'user_name' => 'required|string',
        'email' => 'required|string',
        'password' => 'required|string'
    ];
    protected static $indexColumns = ['user_id', 'user_name', 'email', 'created_at', 'updated_at'];
    protected static $orderBy = ['user_name'];
    protected static $optionColumn = 'user_name';

    public function store()
    {
        Request::merge(['password' => Hash::make(Request::input('password'))]);
        parent::store();
    }

    public function update($id)
    {
        Request::merge(['password' => Hash::make(Request::input('password'))]);
        parent::update($id);
    }
}
