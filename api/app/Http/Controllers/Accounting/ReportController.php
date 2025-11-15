<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Throwable;

class ReportController extends Controller
{
    public function balance()
    {
        $date = Request::input('date', Date('Y-m-d'));

        /* Get accounts */
        $tempAccounts = DB::select(
            "SELECT id,
                type,
                name,
                0 AS balance
            FROM eden.accounts
            WHERE type IN ('A','L','E')
            ORDER BY type, name"
        );
        $accounts = [];
        foreach ($tempAccounts as $account) {
            $accounts[$account->id] = [
                'type' => $account->type,
                'name' => $account->name,
                'balance' => 0
            ];
        }
        $profitLoss = [
            'type' => 'E',
            'name' => 'Profit/Loss',
            'balance' => 0
        ];

        /* Get all transaction up until date */
        $transactions = DB::select(
            "SELECT from_account_id,
                from_account.type AS from_account_type,
                to_account_id,
                to_account.type AS to_account_type,
                amount
            FROM eden.transactions t
            JOIN eden.accounts from_account ON t.from_account_id=from_account.id
            JOIN eden.accounts to_account ON t.to_account_id=to_account.id
            WHERE date <= :date",
            ['date' => $date]
        );
        foreach ($transactions as $transaction) {
            if ($transaction->to_account_type == 'C' || $transaction->to_account_type == 'I') {
                $profitLoss['balance'] -= $transaction->amount;
            } else {
                $accounts[$transaction->to_account_id]['balance'] += (($transaction->to_account_type == 'A') ? 1 : -1) * $transaction->amount;
            }
            if ($transaction->from_account_type == 'C' || $transaction->from_account_type == 'I') {
                $profitLoss['balance'] += $transaction->amount;
            } else {
                $accounts[$transaction->from_account_id]['balance'] -= (($transaction->from_account_type == 'A') ? 1 : -1) * $transaction->amount;
            }
        }

        /* Format accounts */
        $report = (object)[
            'assets' => [],
            'liabilities' => [],
            'equity' => [],
        ];
        foreach ($accounts as $account) {
            if (round($account['balance'] * 100) != 0) {
                if ($account['type'] == 'A') {
                    $report->assets[] = $account;
                } elseif ($account['type'] == 'L') {
                    $report->liabilities[] = $account;
                } else {
                    $report->equity[] = $account;
                }
            }
        }
        $report->equity[] = $profitLoss;
        return $report;
    }

    public function ledger()
    {
        try {
            $from = Request::input('from', Date('Y-01-01'));
            $to = Request::input('to', Date('Y-12-31'));

            $accounts = DB::select(
                "SELECT id, type, name
                FROM eden.accounts
                ORDER BY type, name"
            );

            foreach ($accounts as &$account) {
                $account->opening_balance = DB::selectOne(
                    "SELECT SUM(amount) AS balance
                    FROM eden.transactions
                    WHERE to_account_id=:account AND date<:from",
                    ['account' => $account->id, 'from' => $from]
                )->balance;
                $account->opening_balance -= DB::selectOne(
                    "SELECT SUM(amount) AS balance
                    FROM eden.transactions
                    WHERE from_account_id=:account AND date<:from",
                    ['account' => $account->id, 'from' => $from]
                )->balance;
                $account->closing_balance = $account->opening_balance;
                $account->transactions = DB::select(
                    "   SELECT date,
                            from_account_id,
                            from_account.name AS from_account_name,
                            to_account_id,
                            to_account.name AS to_account_name,
                            note,
                            amount
                        FROM eden.transactions t
                        JOIN eden.accounts from_account ON t.from_account_id=from_account.id
                        JOIN eden.accounts to_account ON t.to_account_id=to_account.id
                        WHERE to_account_id=:account1
                            AND date BETWEEN :from1 AND :to1
                    UNION
                        SELECT date,
                            from_account_id,
                            from_account.name AS from_account_name,
                            to_account_id,
                            to_account.name AS to_account_name,
                            note,
                            -amount
                        FROM eden.transactions t
                        JOIN eden.accounts from_account ON t.from_account_id=from_account.id
                        JOIN eden.accounts to_account ON t.to_account_id=to_account.id
                        WHERE from_account_id=:account2
                            AND date BETWEEN :from2 AND :to2
                    ORDER BY date",
                    [
                        'account1' => $account->id,
                        'from1' => $from,
                        'to1' => $to,
                        'account2' => $account->id,
                        'from2' => $from,
                        'to2' => $to,
                    ]
                );
                foreach ($account->transactions as $transaction) {
                    $account->closing_balance += $transaction->amount;
                }
            }

            return $accounts;
        } catch (Throwable $e) {
            Log::error("{$e->getFile()}:{$e->getLine()} {$e->getCode()}:{$e->getMessage()}\n{$e->getTraceAsString()}");
            throw $e;
        }
    }

    public function result()
    {
        try {
            $from = Request::input('from', Date('Y-01-01'));
            $to = Request::input('to', Date('Y-12-31'));

            $result = (object)[
                'income' => [],
                'cost' => [],
                'unicenta' => []
            ];

            /* Get accounts */
            $accounts = DB::select(
                "SELECT id, type, name
                FROM eden.accounts
                WHERE type IN ('C','I')
                ORDER BY type, name"
            );
            foreach ($accounts as $account) {
                if ($account->type === 'I') {
                    $result->income[$account->id] = [
                        'name' => $account->name,
                        'amount' => 0
                    ];
                } else {
                    $result->cost[$account->id] = [
                        'name' => $account->name,
                        'amount' => 0
                    ];
                }
            }

            /* Get all transaction up until date */
            $transactions = DB::select(
                "SELECT from_account.type AS from_account_type,
                    from_account.id AS from_account_id,
                    to_account.type AS to_account_type,
                    to_account.id AS to_account_id,
                    note,
                    amount
                FROM eden.transactions t
                JOIN accounts from_account ON t.from_account_id=from_account.id
                JOIN accounts to_account ON t.to_account_id=to_account.id
                WHERE t.date BETWEEN :from AND :to",
                ['from' => $from, 'to' => $to]
            );
            foreach ($transactions as $transaction) {
                if ($transaction->from_account_type == 'C') {
                    $result->cost[$transaction->from_account_id]['amount'] -= $transaction->amount;
                } elseif ($transaction->from_account_type == 'I') {
                    $result->income[$transaction->from_account_id]['amount'] += $transaction->amount;
                }
                if ($transaction->to_account_type == 'C') {
                    $result->cost[$transaction->to_account_id]['amount'] += $transaction->amount;
                } elseif ($transaction->to_account_type == 'I') {
                    $result->income[$transaction->to_account_id]['amount'] -= $transaction->amount;
                } else if ($transaction->from_account_id === 3 && $transaction->to_account_id === 62 && $transaction->note === 'Put up to emergency box') {
                    $result->income[34]['amount'] += $transaction->amount;
                }
            }
            $result->income = array_values($result->income);
            $result->cost = array_values($result->cost);

            /* Get total sales from Unicenta */
            $tickets = DB::select(
                "SELECT c.name AS name,
                    ROUND(SUM(tl.price * tl.units * (1+t.rate))) AS amount
                FROM unicentaopos.ticketlines tl
                JOIN unicentaopos.products p ON tl.product=p.id
                JOIN unicentaopos.categories_xref c ON p.category=c.id
                JOIN unicentaopos.taxes t ON tl.taxid=t.id
                JOIN unicentaopos.receipts r ON tl.ticket=r.id
                WHERE r.datenew BETWEEN :from AND :to
                GROUP BY c.name
                ORDER BY c.name",
                ['from' => "$from 00:00:00", 'to' => "$to 23:59:59"]
            );
            foreach ($tickets as $ticket) {
                $result->unicenta[] = $ticket;
            }
            return $result;
        } catch (Throwable $e) {
            Log::error("{$e->getFile()}:{$e->getLine()} {$e->getCode()}:{$e->getMessage()}\n{$e->getTraceAsString()}");
            throw $e;
        }
    }


    public function transactions()
    {
        $params = [
            'from' => Request::input('from', Date('Y-01-01')),
            'to' => Request::input('to', Date('Y-12-31')),
        ];
        $account = Request::input('account', false);
        $accountWhere = '';
        if ($account) {
            $accountWhere = "AND :account IN (from_account_id, to_account_id)";
            $params['account'] = $account;
        }
        $transactions = DB::select(
            "SELECT t.id,
	            date,
	            note,
	            from_account_id,
	            from_account.name AS from_account_name,
	            to_account_id,
	            to_account.name AS to_account_name,
	            amount,
	            official_receipt
            FROM eden.transactions t
            JOIN eden.accounts from_account ON t.from_account_id = from_account.id
            JOIN eden.accounts to_account ON t.to_account_id = to_account.id
            WHERE date BETWEEN :from AND :to
            $accountWhere
            ORDER BY date, id",
            $params
        );
        if (Request::input('hideWithOR', '0') !== '0') {
            foreach ($transactions as $key => $transaction) {
                if (count(glob(config('eden.eden_receipt_dir') . "/{$transaction->id}_*"))) {
                    unset($transactions[$key]);
                }
            }
        }
        return array_values($transactions);
    }

    public function accountTransactions()
    {
        $params = [
            'from' => Request::input('from', Date('Y-01-01')),
            'to' => Request::input('to', Date('Y-12-31')),
            'account' => Request::input('account', false)
        ];

        $balance = DB::selectOne(
            "SELECT SUM(IF(from_account.id=:account2,-amount,amount)) AS opening_balance
            FROM eden.transactions t
            JOIN eden.accounts from_account ON t.from_account_id = from_account.id
            JOIN eden.accounts to_account ON t.from_account_id = to_account.id
            WHERE date < :from AND :account IN (from_account_id, to_account_id)",
            ['from' => $params['from'], 'account' => $params['account'], 'account2' => $params['account']]
        )->opening_balance;


        $transactions = DB::select(
            "SELECT DISTINCT t.id,
                t.date,
                from_account_id,
                from_account.name AS from_account_name,
                to_account_id,
                to_account.name AS to_account_name,
                note,
                t.amount,
                0 AS balance
            FROM eden.transactions t
            JOIN eden.accounts from_account ON t.from_account_id = from_account.id
            JOIN eden.accounts to_account ON t.to_account_id = to_account.id
            WHERE t.date BETWEEN :from AND :to AND :account IN (from_account_id, to_account_id)
            ORDER BY t.date, t.id",
            $params
        );

        $result = [];
        $date = null;
        foreach ($transactions as $transaction) {
            if ($transaction->date != $date) {
                $date = $transaction->date;
                $cash = DB::selectOne(
                    "SELECT amount + safe AS cash,
                        emergency AS emergency
                    FROM eden.cash
                    WHERE date=:date
                    ORDER BY datetime DESC
                    LIMIT 1",
                    ['date' => $date]
                );
                if (!$cash) {
                    $cash = (object) ['cash' => null, 'emergency' => null];
                }
                $result[$date] = [
                    'date' => $date,
                    'amount' => 0,
                    'balance' => 0,
                    'reconcile' => (
                        $params['account'] == 62
                        ? (float) $cash->emergency
                        : (
                            $params['account'] == 1
                            ? (float) $cash->cash
                            : null
                        )
                    ),
                    'transactions' => []
                ];
            }

            if ($transaction->to_account_id == $params['account']) {
                $balance += $transaction->amount;
                $result[$date]['amount'] += $transaction->amount;
            }
            if ($transaction->from_account_id == $params['account']) {
                $balance -= $transaction->amount;
                $result[$date]['amount'] -= $transaction->amount;
            }
            $result[$date]['balance'] = $balance;
            $transaction->balance = $balance;
            $result[$date]['transactions'][] = $transaction;
        }
        return array_values($result);
    }

    public function closedCash()
    {
        $params = [
            'from' => Request::input('from', Date('Y-01-01')),
            'to' => Request::input('to', Date('Y-12-31')),
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
                'cashDiscrepancy' => ($closed[$today]->safe ?? 0) - ($closed[$yesterday]->safe ?? 0) - ($cashTransactions[$today]->amount ?? 0),
                'emergency' => $closed[$today]->emergency ?? 0,
                'emergencyChange' => $emergencyTransactions[$today]->amount ?? 0,
                'emergencyExpected' => ($closed[$yesterday]->emergency ?? 0) + ($emergencyTransactions[$today]->amount ?? 0),
                'emergencyDiscrepancy' => ($closed[$today]->emergency ?? 0) - ($closed[$yesterday]->emergency ?? 0) - ($emergencyTransactions[$today]->amount ?? 0),
            ];
        }
        return $result;
    }
}
