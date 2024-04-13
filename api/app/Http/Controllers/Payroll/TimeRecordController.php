<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\RestController;
use App\Models\Payroll\TimeRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

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

    public function weekOptions()
    {
        $range = DB::selectOne(
            "SELECT MIN(adjusted_timestamp) AS first_date, 
                MAX(adjusted_timestamp) AS last_date
            FROM eden.time_records"
        );

        /* Get first day of week for min and max */
        $firstWeek = strtotime(Date('Y-m-d', strtotime('Last Saturday', strtotime($range->first_date))));
        $lastWeek = strtotime(Date('Y-m-d', strtotime('Last Saturday', strtotime($range->last_date))));
        $data = [];
        for ($date = $lastWeek; $date >= $firstWeek; $date -= 7 * 86400) {
            $data[] = [
                'value' => Date('Y-m-d', $date),
                'label' => Date('Y-m-d', $date) . " - " . Date('Y-m-d', strtotime('Next Friday', $date))
            ];
        }
        return $data;
    }

    public function index()
    {
        $weekOf = Request::input('week', false);
        $from = "$weekOf 00:00:00";
        $to = Date('Y-m-d', strtotime('Next Friday', strtotime($weekOf))) . " 23:59:59";
        return ['data' => DB::select(
            "SELECT DISTINCT employee_id,
                e.name,
                :week as week
            FROM eden.time_records tr
            JOIN eden.employees e ON e.id=tr.employee_id
            WHERE adjusted_timestamp BETWEEN :from AND :to
            ORDER BY e.name",
            ['from' => $from, 'to' => $to, 'week' => $weekOf]
        )];
    }

    public function showEmployeeWeek($employeeId, $weekOf)
    {
        $from = "$weekOf 00:00:00";
        $to = Date('Y-m-d', strtotime('Next Friday', strtotime($weekOf))) . " 23:59:59";
        return DB::select(
            "SELECT tr.*,
                bs.name AS biometric_status_name
            FROM eden.time_records tr
            LEFT JOIN eden.biometric_statuses bs ON bs.id = tr.biometric_status
            WHERE employee_id=:employeeId
                AND adjusted_timestamp BETWEEN :from AND :to
            ORDER BY adjusted_timestamp",
            ['employeeId' => $employeeId, 'from' => $from, 'to' => $to]
        );
    }

    public function store()
    {
        $timeRecords = Request::all();
        foreach ($timeRecords as $timeRecord) {
            if ($timeRecord['id']) {
                DB::update(
                    "UPDATE eden.time_records
                    SET adjusted_timestamp=:timestamp,
                        hide=:hide
                    WHERE id=:id",
                    ['timestamp' => $timeRecord['adjusted_timestamp'], 'hide' => $timeRecord['hide'], 'id' => $timeRecord['id']]
                );
            } else {
                DB::insert(
                    "INSERT INTO eden.time_records (employee_id, adjusted_timestamp, hide)
                    VALUES (:employee_id, :adjusted_timestamp, :hide)",
                    [
                        'employee_id' => $timeRecord['employee_id'],
                        'adjusted_timestamp' => $timeRecord['adjusted_timestamp'],
                        'hide' => $timeRecord['hide']
                    ]
                );
            }
        }
    }
}
