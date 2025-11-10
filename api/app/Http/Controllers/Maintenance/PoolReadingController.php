<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\RestController;
use App\Models\Maintenance\PoolReading;

class PoolReadingController extends RestController
{
    protected static $model = PoolReading::class;
    protected static $validations = [
        'date' => 'required|date',
        'free_chlorine' => 'nullable|numeric',
        'ph' => 'nullable|numeric',
        'alkalinity' => 'nullable|numeric',
        'total_chlorine' => 'nullable|numeric',
        'hardness' => 'nullable|numeric',
        'cyanuric_acid' => 'nullable|numeric',
    ];
    protected static $indexColumns = ['id', 'date', 'free_chlorine', 'ph', 'alkalinity', 'total_chlorine', 'hardness', 'cyanuric_acid', 'created_at', 'updated_at'];
    protected static $orderBy = ['date'];
}
