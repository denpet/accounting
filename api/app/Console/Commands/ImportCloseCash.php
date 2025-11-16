<?php

namespace App\Console\Commands;

use App\Models\Accounting\Transaction;
use App\Models\Unicenta\ImportedClosedCash;
use Illuminate\Console\Command;
use Illuminate\Database\UniqueConstraintViolationException;
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
                    SUM(
                    	IF(
                    		ticketlines.price IS NULL,
                    		payments.total,
                    		ticketlines.units * ticketlines.price * (1 + taxes.rate)
                    	)
                    ) AS total,
                    CASE
                    	WHEN products.category='d82a29aa-2703-423c-8e1f-67162a8018dc' THEN 'accommodation'
                    	ELSE 'sales'
                    END AS type
                FROM unicentaopos.closedcash
                JOIN unicentaopos.receipts on receipts.money=closedcash.money
                JOIN unicentaopos.payments ON payments.receipt=receipts.id
                LEFT JOIN unicentaopos.tickets ON tickets.id=receipts.id
                LEFT JOIN unicentaopos.ticketlines ON tickets.id=ticketlines.ticket
                LEFT JOIN unicentaopos.products ON products.id=ticketlines.product
                LEFT JOIN unicentaopos.taxes ON taxes.id=ticketlines.taxid
                WHERE closedcash.money NOT IN (SELECT id FROM eden.imported_closed_cash)
                	AND closedcash.dateend IS NOT NULL
                GROUP BY closedcash.host,
                    closedcash.hostsequence,
                    closedcash.money,
                    closedcash.datestart,
                    closedcash.dateend,
                    payments.payment,
                    type
                ORDER BY closedcash.host,
                    closedcash.hostsequence"
            );

            foreach ($closedCashRows as $closedCashRow) {
                $transaction = [];
                $transaction['date'] = substr($closedCashRow->dateend, 0, 10);

                switch ($closedCashRow->payment) {
                    // case 'bank':
                    //     $transaction['note'] = "Deposit, ";
                    //     $transaction['to_account_id'] = 2; // Bank
                    //     break;

                    case 'ccard':
                        $transaction['note'] = "Credit card sales, ";
                        $transaction['to_account_id'] = 61; // Tab!
                        break;

                    case 'ccardrefund':
                        $transaction['note'] = "Credit card refund, ";
                        $transaction['to_account_id'] = 61; // Tab!
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
                        $transaction['note'] = null;
                        $transaction['to_account_id'] = 62; // Emergency
                        break;

                    default:
                        continue 2;
                }

                if ($transaction['to_account_id'] === 62) {
                    $transaction['from_account_id'] = $closedCashRow->type === 'accommodation' ? 5 : 34;
                    $transaction['note'] = "Put up to emergency box";
                    $offset = date('Y9', strtotime($transaction['date']));
                } else {
                    $transaction['from_account_id'] = $closedCashRow->type === 'accommodation' ? 5 : 34;
                    $transaction['note'] .= "$closedCashRow->host, $closedCashRow->hostsequence";
                    $offset = date('Y1', strtotime($transaction['date']));
                }

                /* Create offset for number series */
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
                $transaction['amount'] = round($closedCashRow->total, 2);
                if ($transaction['amount'] < 0) {
                    $temp = $transaction['from_account_id'];
                    $transaction['from_account_id'] = $transaction['to_account_id'];
                    $transaction['to_account_id'] = $temp;
                    $transaction['amount'] = -$transaction['amount'];
                }

                Transaction::create($transaction);
                try {
                    ImportedClosedCash::create(['id' => $closedCashRow->money]);
                } catch (UniqueConstraintViolationException $e) {
                }
                $this->line("Saved transaction");
            }
        } catch (Throwable $e) {
            $className = class_basename(get_class($e));
            Log::error("{$className} {$e->getFile()}:{$e->getLine()} {$e->getCode()}:{$e->getMessage()}\n{$e->getTraceAsString()}");
            $this->line($e->getMessage());
        }

        $time0 = microtime(true);
        $this->line(sprintf("(%.0f seconds)", microtime(true) - $time0));
    }
}
