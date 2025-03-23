<?php

namespace App\Http\Controllers\Unicenta;

use App\Http\Controllers\RestController;
use App\Models\Unicenta\Product;
use App\Models\Unicenta\StockCurrent;
use App\Models\Unicenta\StockDiary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class ProductController extends RestController
{
    const LOCATION = 0;

    protected static $model = Product::class;
    protected static $validations = [
        'name' => 'required|string',
    ];
    protected static $indexColumns = ['id', 'name'];
    protected static $orderBy = ['name'];
    protected static $optionColumn = 'name';

    function pricebuyIndex()
    {
        return DB::connection('unicenta')->select(
            "SELECT p.id,
                p.name,
                c.name AS category_name,
                ROUND(p.pricebuy * 1.12, 2) as pricebuy
            FROM products p
            JOIN products_cat pc ON p.id=pc.product
            JOIN categories c ON p.category = c.id
            WHERE c.name in (
                    'Bar Stock',
                    'Beer',
                    'Canned / Bottled',
                    'Cigarettes',
                    'Fruit',
                    'Intern products',
                    'Wine',
                    'Fruit',
                    'Kitchen stock',
                    'Stock Items',
                    'Wine'
                )
            ORDER BY c.name, p.name"
        );
    }

    function pricebuyUpdate($id)
    {
        $data = Request::validate([
            'pricebuy' => "required|numeric"
        ]);
        $product = Product::find($id);
        $product->pricebuy = $data['pricebuy'] / 1.12;
        $product->save();
    }

    function registerPurchase($id)
    {
        $data = Request::validate([
            'quantity' => "required|numeric",
            'amount' => "required|numeric"
        ]);

        /* Update pricebuy */
        $product = Product::find($id);
        $product->pricebuy = ($data['amount'] / $data['quantity']) / 1.12;
        $product->save();

        /* Update stock */
        $stockCurrent = StockCurrent::find($id);
        $stockCurrent->units += $data['quantity'];
        $stockCurrent->save();

        /* Update stock diary */
        StockDiary::create([
            'id' => Str::uuid(),
            'datenew' => Date('Y-m-d H:i:s'),
            'reason' => StockDiary::REASON_IN_PURCHASE,
            'location' => static::LOCATION,
            'product' => $id,
            'units' => $data['quantity'],
            'price' => $product->pricebuy,
            'appuser' => Auth::user()->name
        ]);
    }

    function cycleCountIndex()
    {
        return DB::connection('unicenta')->select(
            "SELECT p.id,
                p.name,
                c.name AS category_name
            FROM products p
            JOIN products_cat pc ON p.id=pc.product
            JOIN categories c ON p.category = c.id
            WHERE c.name in (
                    'Bar Stock',
                    'Beer',
                    'Canned / Bottled',
                    'Cigarettes',
                    'Fruit',
                    'Intern products',
                    'Wine',
                    'Fruit',
                    'Kitchen stock',
                    'Stock Items',
                    'Wine'
                )
            ORDER BY c.name, p.name"
        );
    }

    function registerCycleCount($id)
    {
        $data = Request::validate(['quantity' => "required|numeric"]);

        $product = Product::find($id);

        /* Update stock */
        $stockCurrent = StockCurrent::find($id);
        $adjustment = $data['quantity'] - $stockCurrent->units;
        $stockCurrent->units = $data['quantity'];
        $stockCurrent->save();

        /* Update stock diary */
        StockDiary::create([
            'id' => Str::uuid(),
            'datenew' => Date('Y-m-d H:i:s'),
            'reason' => StockDiary::REASON_CYCLE_COUNT,
            'location' => static::LOCATION,
            'product' => $id,
            'units' => $adjustment,
            'price' => $product->pricebuy,
            'appuser' => Auth::user()->name
        ]);
    }
}
