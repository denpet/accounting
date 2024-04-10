<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\RestController;
use App\Models\Payroll\Employee;

class EmployeeController extends RestController
{
    protected static $model = Employee::class;
    protected static $validations = [
        'id' => 'required|numeric',
        'name' => 'required|string',
        'rate' => 'required|numeric',
        'active' => 'required|in:0,1,2',
    ];
    protected static $indexColumns = ['id', 'name', 'rate', 'active'];
    protected static $orderBy = ['name'];
    protected static $optionColumn = 'name';
}
