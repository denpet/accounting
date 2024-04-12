<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\RestController;
use App\Models\Payroll\TimeRecord;

class TimeRecordController extends RestController
{
    protected static $model = TimeRecord::class;
    protected static $validations = [
        'id' => 'required|numeric',
        'employee_id' => 'required|exists:.eden.employees,id',
        'biometric_timestamp' => 'required|datetime',
        'biometric_status' => 'required|in:1,3,15',
        'adjusted_timestamp' => 'required|datetime',
        'hide' => 'required|boolean',
    ];
    protected static $indexColumns = ['id', 'biometric_timestamp', 'biometric_status', 'adjusted_timestamp', 'hide', 'employee_id'];
    protected static $with = ['employee:id,name', 'biometricStatus:id,name'];
    protected static $orderBy = ['employee_id', 'biometric_timestamp'];
}
