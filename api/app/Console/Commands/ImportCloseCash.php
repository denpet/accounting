<?php

namespace App\Console\Commands;

use App\Models\Accounting\Transaction;
use App\Models\Unicenta\ImportedClosedCash;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ImportCloseCash extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unicenta:import-close-cash';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import close cash from Unicenta';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            /* Get next menu item code */
            $closedCashRows = DB::select(
                "SELECT closedcash.money,
                    closedcash.host,
                    closedcash.hostsequence,
                    closedcash.dateend,
                    payments.payment,
                    SUM(payments.total) AS total
                FROM unicentaopos.closedcash
                JOIN unicentaopos.receipts on receipts.money=closedcash.money
                JOIN unicentaopos.payments ON payments.receipt=receipts.id
                WHERE closedcash.money NOT IN (SELECT id FROM eden.imported_closed_cash)
                AND closedcash.dateend IS NOT NULL
                GROUP BY closedcash.host,
                    closedcash.hostsequence,
                    closedcash.money,
                    closedcash.datestart,
                    closedcash.dateend,
                    payments.payment
                ORDER BY closedcash.host,
                    closedcash.hostsequence"
            );

            foreach ($closedCashRows as $closedCashRow) {
                $transaction = []; //Transaction::create();
                $transaction['date'] = substr($closedCashRow->dateend, 0, 10);

                /* Create offset for number series */
                $offset = date('Y1', strtotime($transaction['date']));
                $id = DB::selectOne(
                    "SELECT MAX(id) AS id
                    FROM eden.transactions
                    where id BETWEEN {$offset}00000 AND {$offset}99999"
                )->id;
                if (!isset($id)) {
                    $id = $offset . '00001';
                } else {
                    $id++;
                }
                $transaction['id'] = $id;
                $transaction['from_account_id'] = 34;
                $skip = false;
                switch ($closedCashRow->payment) {
                    case 'bank':
                        $transaction['note'] = "Deposit, ";
                        $transaction['to_account_id'] = 2; // Bank
                        break;

                    case 'ccard':
                        $transaction['note'] = "Credit card sales, ";
                        $transaction['to_account_id'] = 16; // Credit card
                        break;

                    case 'ccardrefund':
                        $transaction['note'] = "Credit card refund, ";
                        $transaction['to_account_id'] = 16; // Credit card
                        break;

                    case 'cashout':
                        $transaction['note'] = "Payment, ";
                        $transaction['to_account_id'] = 1; // Cash
                        break;

                    case 'cashrefund':
                        $transaction['note'] = "Refund, ";
                        $transaction['to_account_id'] = 1; // Cash
                        break;

                    case 'paperin':
                        $transaction['note'] = "Voucher sales, ";
                        $transaction['to_account_id'] = 1; // Cash
                        break;

                    case 'cash':
                        $transaction['note'] = "Sales, ";
                        $transaction['to_account_id'] = 1; // Cash
                        break;

                    case 'cheque':
                        $transaction['note'] = "Emergency, ";
                        $transaction['to_account_id'] = 62; // Cash
                        break;

                    default:
                        $skip = true;
                }
                if (! $skip) {
                    $transaction['note'] .= "$closedCashRow->host, $closedCashRow->hostsequence";
                    $transaction['amount'] = round($closedCashRow->total, 2);
                    if ($transaction['amount'] < 0) {
                        $temp = $transaction['from_account_id'];
                        $transaction['from_account_id'] = $transaction['to_account_id'];
                        $transaction['to_account_id'] = $temp;
                        $transaction['amount'] = -$transaction['amount'];
                    }
                    if ($transaction['to_account_id'] === 62) {
                        $transaction['from_account_id'] = 3;
                        $transaction['note'] = 'Put up to emergency box';
                    }
                    Transaction::create($transaction); //$transaction->save();
                    echo "Saved transaction\n";
                }

                $importedClosedCash = ImportedClosedCash::find($closedCashRow->money);
                if ($importedClosedCash == null) {
                    $importedClosedCash = ImportedClosedCash::create();
                    $importedClosedCash['id'] = $closedCashRow->money;
                    $importedClosedCash->save();
                }
            }

            // $this->sendStatement ();
        } catch (Throwable $e) {
            echo sprintf("Error: " . $e->getMessage());
            var_dump($closedCashRow);
        }

        $time0 = microtime(true);
        echo sprintf("(%.0f seconds)\n", microtime(true) - $time0);
    }
}
