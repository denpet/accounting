<?php

namespace App\Http\Controllers\Unicenta\Report;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class CostIncomeController extends Controller
{
    public function show()
    {
        $params = [
            'from' => Request::input('from_date', Date('Y-m-d')),
            'to' => Request::input('to_date', Date('Y-m-d'))
        ];
        return DB::connection('unicenta')->select(
            "SELECT p.id,
                c.name AS category_name,
                p.name,
                SUM(units) AS units,
                SUM(pricebuy * units) AS cost,
                SUM(tl.units* tl.price * (1 + tx.rate)) AS income
            FROM receipts r
            JOIN tickets t ON r.id=t.id
            JOIN ticketlines tl ON tl.ticket=t.id
            JOIN products p ON p.id=tl.product
            JOIN categories c ON c.id=p.category
            JOIN taxes tx ON tx.id=p.taxcat
            WHERE datenew BETWEEN :from AND :to
            GROUP BY p.id,c.name, p.name
            ORDER BY c.name, p.name",
            $params
        );
    }
}
