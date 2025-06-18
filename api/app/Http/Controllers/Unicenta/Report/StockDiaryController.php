<?php

namespace App\Http\Controllers\Unicenta\Report;

use App\Http\Controllers\Controller;
use App\Models\Unicenta\StockDiary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class StockDiaryController extends Controller
{
    public function show()
    {
        $params = [
            'from' => Request::input('from_date', Date('Y-m-d')),
            'to' => Request::input('to_date', Date('Y-m-d')),
            'product' => Request::input('product', null)
        ];
        $where = ['datenew BETWEEN :from AND :to'];
        if ($params['product']) {
            $where[] = "p.id=:product";
        } else {
            unset($params['product']);
        }
        $where = "WHERE " . implode(" AND ", $where);
        $entries = DB::connection('unicenta')->select(
            "SELECT p.id,
                name,
                DATE(sd.datenew) AS date,
                sd.reason,
                sc.units AS current_stock,
                SUM(sd.units) AS change_stock
            FROM unicentaopos.products p
            JOIN unicentaopos.stockcurrent sc ON p.id=sc.product
            JOIN unicentaopos.stockdiary sd ON sd.product=p.id
            $where
            GROUP BY p.id, p.name, sc.units, reason, DATE(sd.datenew)
            ORDER BY p.name,p.id,sd.datenew DESC",
            $params
        );
        $result = [];
        $current = false;
        $stockChange = 0;
        foreach ($entries as $entry) {
            if (!$current || $current['id'] !== $entry->id) {
                if ($current) {
                    $result[] = $current;
                }
                $current = [
                    'id' => $entry->id,
                    'name' => $entry->name,
                    'days' => []
                ];
            }
            if (!isset($current['days'][0]) || $current['days'][0]['date'] !== $entry->date) {
                array_unshift($current['days'], [
                    'date' => $entry->date,
                    'purchase' => 0,
                    'refund' => 0,
                    'sale' => 0,
                    'break' => 0,
                    'movement' => 0,
                    'adjustment' => 0,
                    'other' => 0,
                    'stock' => isset($current['days'][0]) ? $current['days'][0]['stock'] + $stockChange : $entry->current_stock
                ]);
                $stockChange = 0;
            }
            switch ($entry->reason) {
                case StockDiary::REASON_IN_PURCHASE:
                    $current['days'][0]['purchase'] += $entry->change_stock;
                    break;

                case StockDiary::REASON_IN_REFUND:
                case StockDiary::REASON_OUT_REFUND:
                    $current['days'][0]['refund'] += $entry->change_stock;
                    break;

                case StockDiary::REASON_IN_MOVEMENT:
                case StockDiary::REASON_OUT_MOVEMENT:
                    $current['days'][0]['movement'] += $entry->change_stock;
                    break;

                case StockDiary::REASON_OUT_SALE:
                    $current['days'][0]['sale'] += $entry->change_stock;
                    break;

                case StockDiary::REASON_OUT_BREAK:
                    $current['days'][0]['break'] += $entry->change_stock;
                    break;

                case StockDiary::REASON_CYCLE_COUNT:
                    $current['days'][0]['adjustment'] += $entry->change_stock;
                    break;

                case StockDiary::REASON_OUT_SAMPLE:
                case StockDiary::REASON_OUT_FREE:
                case StockDiary::REASON_OUT_USED:
                case StockDiary::REASON_OUT_SUBTRACT:
                case StockDiary::REASON_OUT_CROSSING:
                    $current['days'][0]['other'] += $entry->change_stock;
                    break;
            }
            $stockChange -= $entry->change_stock;
        }
        if ($current) {
            $result[] = $current;
        }
        return $result;
    }
}
