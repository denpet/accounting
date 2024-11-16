<?php

namespace App\Http\Controllers\Unicenta\Report;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class StatementOfAccountController extends Controller
{

    public function show($id)
    {
        $data = ['tickets' => [], 'ticket_total' => 0, 'payment_total' => 0];
        $data['customer'] = DB::connection('unicenta')->selectOne(
            "SELECT name, 
                address,
                address2,
                postal,
                city,
                region,
                country
            FROM customers
            WHERE id=:id",
            ['id' => $id]
        );

        /* Tickets */
        $ticketRows = DB::connection('unicenta')->select(
            "SELECT t.ticketid, 
                p.name, 
                tl.units, 
                tl.price * (1+ta.rate) as price,
                r.datenew
            FROM tickets t 
            JOIN ticketlines tl on t.id=tl.ticket
            JOIN products p on tl.product=p.id
            JOIN taxes ta on tl.taxid=ta.id
            JOIN receipts r on t.id=r.id
            WHERE t.customer=:id
            ORDER BY t.ticketid, tl.line",
            ['id' => $id]
        );
        $ticket = null;
        foreach ($ticketRows as $ticketRow) {
            if ($ticket === null) {
                $ticket = ['id' => $ticketRow->ticketid, 'date' => $ticketRow->datenew, 'rows' => [], 'total' => 0];
            }
            if ($ticketRow->ticketid !== $ticket['id']) {
                $data['tickets'][] = $ticket;
                $ticket = ['id' => $ticketRow->ticketid, 'date' => $ticketRow->datenew, 'rows' => [], 'total' => 0];
            }
            $ticket['rows'][] = ['name' => $ticketRow->name, 'units' => $ticketRow->units, 'price' => $ticketRow->price, 'date' => $ticketRow->datenew];
            $ticket['total'] += $ticketRow->units * $ticketRow->price;
            $data['ticket_total'] += $ticketRow->units * $ticketRow->price;
        }
        if ($ticket !== null) {
            $data['tickets'][] = $ticket;
        }

        /* Payments */
        $data['payments'] = DB::connection('unicenta')->select(
            "SELECT p.payment, 
                p.total, 
                r.datenew AS date
            FROM payments p
            JOIN receipts r on p.receipt = r.id
            JOIN tickets t on p.receipt = t.id
            WHERE t.customer = :id 	
                AND p.payment not in ('debt','debtpaid')
                AND p.total>0.01
            ORDER BY r.datenew",
            ['id' => $id]
        );
        foreach ($data['payments'] as $payment) {
            $data['payment_total'] += $payment->total;
        }
        $data['total'] = $data['ticket_total'] - $data['payment_total'];

        return view('unicenta.reports.statement-of-account', $data);
    }
}
