<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
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
            WHERE date <= :date
                AND date>='2024-10-01'",
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

            $result = (object)[
                'report' => [],
                'total' => [
                    'I' => 0,
                    'C' => 0
                ],
                'unicenta' => [
                    'income' => [],
                    'total' => 0
                ]
            ];

            /* Get accounts */
            $accounts = DB::select(
                "SELECT id, type, name
                FROM eden.accounts
                WHERE type IN ('C','I')
                ORDER BY type, name"
            );
            foreach ($accounts as $account) {
                $result->report[$account->type][$account->id] = [
                    'name' => $account->name,
                    'amount' => 0
                ];
            }

            /* Get all transaction up until date */
            $transactions = DB::select(
                "SELECT from_account.type AS from_account_type,
                    from_account.id AS from_account_id,
                    to_account.type AS to_account_type,
                    to_account.id AS to_account_id,
                    amount                
                FROM eden.transactions t
                JOIN accounts from_account ON t.from_account_id=from_account.id
                JOIN accounts to_account ON t.to_account_id=to_account.id
                WHERE t.date BETWEEN :from AND :to",
                ['from' => $from, 'to' => $to]
            );
            foreach ($transactions as $transaction) {
                if ($transaction->from_account_type == 'C') {
                    $result->report['C'][$transaction->from_account_id]['amount'] -= $transaction->amount;
                    $result->total['C'] -= $transaction->amount;
                } elseif ($transaction->from_account_type == 'I') {
                    $result->report['I'][$transaction->from_account_id]['amount'] += $transaction->amount;
                    $result->total['I'] += $transaction->amount;
                }
                if ($transaction->to_account_type == 'C') {
                    $result->report['C'][$transaction->to_account_id]['amount'] += $transaction->amount;
                    $result->total['C'] += $transaction->amount;
                } elseif ($transaction->to_account_type == 'I') {
                    $result->report['I'][$transaction->to_account_id]['amount'] -= $transaction->amount;
                    $result->total['I'] -= $transaction->amount;
                }
            }

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
                $result->unicenta['income'][] = $ticket;
                $result->unicenta['total'] += $ticket->amount;
            }
            $result->total['total'] = $result->total['I'] - $result->total['C'];
            $result->total['totalUnicenta'] = $result->unicenta['total'] - $result->total['C'];

            return $result;
        } catch (Throwable $e) {
            Log::error("{$e->getFile()}:{$e->getLine()} {$e->getCode()}:{$e->getMessage()}\n{$e->getTraceAsString()}");
            throw $e;
        }
    }
}
