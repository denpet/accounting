<?php

namespace App\Http\Controllers\Unicenta;

use App\Http\Controllers\RestController;
use App\Models\Unicenta\Product;
use App\Models\Unicenta\StockCurrent;
use App\Models\Unicenta\StockDiary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class ProductController extends RestController
{
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

    private function uuidv4()
    {
        $data = random_bytes(16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
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
            'id' => $this->uuidv4(),
            'datenew' => Date('Y-m-d H:i:s'),
            'reason' => 1,
            'location' => 0,
            'product' => $id,
            'units' => $data['quantity'],
            'price' => $product->pricebuy,
            'appuser' => Auth::user()->name
        ]);
    }
}
