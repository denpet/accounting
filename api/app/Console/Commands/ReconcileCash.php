<?php

namespace App\Console\Commands;

use App\Http\Controllers\Accounting\ReportController;
use App\Mail\CashDiscrepancy;
use App\Models\Accounting\Transaction;
use App\Models\Unicenta\ImportedClosedCash;
use DateTime;
use Illuminate\Console\Command;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ReconcileCash extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reconcile:cash';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconcile cash from Unicenta';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $errors = [];
        try {
            /* Check that cash is closed */
            if (DB::selectOne(
                "SELECT COUNT(*) AS count
                FROM unicentaopos.closedcash
                WHERE dateend BETWEEN '$yesterday 12:00' AND '$today 12:00'"
            )->count === 0) {
                $errors[] = "Cash not closed $yesterday";
                $this->message($errors);
                return;
            }

            /* Check that cash was reported */
            if (DB::selectOne(
                "SELECT COUNT(*) AS count
                FROM cash
                WHERE date = '$yesterday'"
            )->count === 0) {
                $errors[] = "No cash reported $yesterday";
                $this->message($errors);
                return;
            }

            /* Check for discrepancies the last month */
            $from = (new DateTime($yesterday))->modify('-1 month')->format('Y-m-d');
            $closedCash = $this->getClosedCash($from, $yesterday);
            foreach ($closedCash as $cash) {
                if (abs($cash['cashDiscrepancy']) > 100 && abs($cash['emergencyDiscrepancy']) > 100) {
                    $errors[] = "Discrepancy found on {$cash['date']}: cash discrepancy {$cash['cashDiscrepancy']}, emergency discrepancy {$cash['emergencyDiscrepancy']}\n";
                } elseif (abs($cash['cashDiscrepancy']) > 100) {
                    $errors[] = "Discrepancy found on {$cash['date']}: cash discrepancy {$cash['cashDiscrepancy']}\n";
                } elseif (abs($cash['emergencyDiscrepancy']) > 100) {
                    $errors[] = "Discrepancy found on {$cash['date']}: emergency discrepancy {$cash['emergencyDiscrepancy']}\n";
                }
            }
            if (count($errors) > 0) {
                $this->message($errors);
            }
        } catch (Throwable $e) {
            $className = class_basename(get_class($e));
            Log::error("{$className} {$e->getFile()}:{$e->getLine()} {$e->getCode()}:{$e->getMessage()}\n{$e->getTraceAsString()}");
            $this->line($e->getMessage());
        }

        $time0 = microtime(true);
        $this->line(sprintf("(%.0f seconds)", microtime(true) - $time0));
    }

    private function getClosedCash($from, $to)
    {
        $params = [
            'from' => $from,
            'to' => $to
        ];

        $cashTransactions = DB::select(
            "SELECT date,
                SUM(IF(from_account_id=1,-amount,amount)) AS amount
            FROM eden.transactions
            WHERE date BETWEEN :from AND :to AND 1 IN (from_account_id, to_account_id)
            GROUP BY date
            ORDER BY date",
            $params
        );
        $cashTransactions = array_combine(array_column($cashTransactions, 'date'), $cashTransactions);

        $emergencyTransactions = DB::select(
            "SELECT date,
                SUM(IF(from_account_id=62,-amount,amount)) AS amount
            FROM eden.transactions
            WHERE date BETWEEN :from AND :to AND 62 IN (from_account_id, to_account_id)
            GROUP BY date
            ORDER BY date",
            $params
        );
        $emergencyTransactions = array_combine(array_column($emergencyTransactions, 'date'), $emergencyTransactions);

        $yesterday = (new DateTime($params['from']))->modify('-1 day')->format('Y-m-d');
        $closed = DB::select(
            "SELECT date,
            	safe,
            	emergency
            FROM cash c1
            WHERE date BETWEEN :from AND :to
            	AND datetime IN (
	            	SELECT MAX(datetime)
	            	FROM cash c2
	            	WHERE c2.date=c1.date
	            )
            ORDER BY date",
            [
                'from' => $yesterday,
                'to' => $params['to']
            ]
        );
        $closed = array_combine(array_column($closed, 'date'), $closed);

        $result = [];
        for ($date = new DateTime($params['from']); $date <= new DateTime($params['to']); $date->modify('+1 day')) {
            $today = $date->format('Y-m-d');
            $yesterday = (new DateTime($today))->modify('-1 day')->format('Y-m-d');
            $result[] = [
                'date' => $today,
                'cash' => $closed[$today]->safe ?? 0,
                'cashChange' => $cashTransactions[$today]->amount ?? 0,
                'cashExpected' => ($closed[$yesterday]->safe ?? 0) + ($cashTransactions[$today]->amount ?? 0),
                'cashDiscrepancy' => round(($closed[$today]->safe ?? 0) - ($closed[$yesterday]->safe ?? 0) - ($cashTransactions[$today]->amount ?? 0)),
                'emergency' => $closed[$today]->emergency ?? 0,
                'emergencyChange' => $emergencyTransactions[$today]->amount ?? 0,
                'emergencyExpected' => ($closed[$yesterday]->emergency ?? 0) + ($emergencyTransactions[$today]->amount ?? 0),
                'emergencyDiscrepancy' => round(($closed[$today]->emergency ?? 0) - ($closed[$yesterday]->emergency ?? 0) - ($emergencyTransactions[$today]->amount ?? 0)),
            ];
        }
        return $result;
    }

    private function message($errors)
    {
        Mail::to('info@eden.ph')
            ->send(new CashDiscrepancy($errors));
    }
}
