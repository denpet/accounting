<?php

namespace App\Console\Commands;

use App\Models\TimeAttendance\Employee;
use App\Models\TimeAttendance\TimeRecord;
use App\Services\ZKTecoService;
use App\ZKLibrary\ZKLib;
use Illuminate\Console\Command;

class ZKTecoSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zkteco:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync data with ZKTeco';

    /**
     * Execute the console command.
     */
    public function handle(ZKTecoService $zk)
    {
        $zk->connect(config('eden.zk_host'), config('eden.zk_port', 4370));

        /* Get Users */
        $users = $zk->getUser();
        foreach ($users as $user) {
            $employee = Employee::find($user[0]) ?: Employee::create(['id' => $user[0], 'name' => $user[1], 'rate' => 0, 'active' => 2]);
            $employee->name = $user[1];
            $employee->save();
        }

        /* Get Attendance */
        $attendances = $zk->getAttendance();
        foreach ($attendances as $attendance) {
            if ($attendance[1] !== 0) {
                $timeRecord = TimeRecord::find(
                    $attendance[0]
                ) ?: TimeRecord::create(
                    [
                        'id' => $attendance[0],
                        'employee_id' => $attendance[1],
                        'biometric_status' => $attendance[2],
                        'biometric_timestamp' => $attendance[3],
                        'adjusted_status' => $attendance[2],
                        'adjusted_timestamp' => $attendance[3]
                    ]
                );
                $timeRecord->employee_id = $attendance[1];
                $timeRecord->biometric_status = $attendance[2];
                $timeRecord->biometric_timestamp = $attendance[3];
                $timeRecord->save();
            }
        }
        $zk->disconnect();
    }
}