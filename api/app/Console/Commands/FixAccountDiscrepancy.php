<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class FixAccountDiscrepancy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unicenta:fix-account-discrepancy';

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
            /* Select total sales per customer */
            $sales = DB::connection('unicenta')->select(
                "SELECT c.id,
                    c.name,
                    ROUND(IFNULL(c.curdebt,0)) AS curdebt,
                    ROUND(SUM(tl.units * tl.price * (1+ta.rate))) AS sales
                FROM customers c
                INNER JOIN tickets t ON t.customer=c.id
                INNER JOIN ticketlines tl ON t.id=tl.ticket
                INNER JOIN products p ON tl.product=p.id
                INNER JOIN taxes ta ON tl.taxid=ta.id
                INNER JOIN receipts r ON t.id=r.id
                WHERE c.visible=1
                GROUP BY c.name, c.id, c.curdebt
                ORDER BY c.name"
            );
            $result = [];
            foreach ($sales as $sale) {
                $result[$sale->id] = array(
                    'id' => $sale->id,
                    'name' => $sale->name,
                    'curdebt' => $sale->curdebt,
                    'sales' => $sale->sales,
                    'payments' => 0
                );
                $result[$sale->id]['truedebt'] = $result[$sale->id]['sales'] - $result[$sale->id]['payments'];
            }

            /* Select total payments per customer */
            $payments = DB::connection('unicenta')->select(
                "SELECT c.id,
                    c.name,
                    ROUND(IFNULL(c.curdebt,0)) AS curdebt,
                    ROUND(SUM(p.total)) AS amount
                FROM payments p
                INNER JOIN receipts r ON p.receipt = r.id
                INNER JOIN tickets t ON p.receipt = t.id
                INNER JOIN customers c ON t.customer=c.id
                WHERE p.payment NOT IN ('debt','debtpaid')
                    AND c.visible=1
                GROUP BY c.id, c.name, curdebt"
            );
            foreach ($payments as $payment) {
                if (! isset($result[$payment->id])) {
                    $result[$payment->id] = array(
                        'id' => $payment->id,
                        'name' => $payment->name,
                        'curdebt' => $payment->curdebt,
                        'sales' => 0
                    );
                }
                $result[$payment->id]['payments'] = $payment->amount;
                $result[$payment->id]['truedebt'] = $result[$payment->id]['sales'] - $result[$payment->id]['payments'];
            }

            /* Correct curdebt */
            foreach ($result as $statement) {
                if ($statement['truedebt'] != $statement['curdebt']) {
                    $this->line("Correcting '{$statement['name']}' from {$statement['curdebt']} to {$statement['truedebt']}");
                    DB::connection('unicenta')->update(
                        "UPDATE customers
                        SET curdebt=:curdebt
                        WHERE id=:id",
                        [
                            'curdebt' => $statement['truedebt'],
                            'id' => $statement['id']
                        ]
                    );
                }
            }
        } catch (Throwable $e) {
            Log::error($e->getMessage());
        }
        $time0 = microtime(true);
        $this->line(sprintf("(%.0f seconds)", microtime(true) - $time0));
    }
}
