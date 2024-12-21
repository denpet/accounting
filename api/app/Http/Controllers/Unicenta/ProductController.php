<?php

namespace App\Http\Controllers\Unicenta;

use App\Http\Controllers\RestController;
use App\Models\Unicenta\Product;
use App\Models\Unicenta\ProductBundle;
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
                p.pricebuy
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
        $product->pricebuy = $data['pricebuy'];
        $product->save();
    }

    function bundleUpdate($id)
    {
        $data = Request::validate([
            'product_bundle' => 'required|exists:.unicentaopos.products,id',
            'quantity' => "required|numeric"
        ]);
        $product = ProductBundle::find($id);
        $product->product_bundle = $data['product_bundle'];
        $product->quantity = $data['quantity'];
        $product->save();
    }
}
