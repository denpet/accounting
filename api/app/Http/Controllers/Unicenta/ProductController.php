<?php

namespace App\Http\Controllers\Unicenta;

use App\Http\Controllers\RestController;
use App\Models\Unicenta\Customer;
use App\Models\Unicenta\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class ProductController extends RestController
{
    protected static $model = Customer::class;
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
            WHERE catshowname
	            AND c.name in (
                    'Pasta',
                    'Salad',
                    'Soup',
                    'Canned / Bottled',
                    'International',
                    'Fresh Juice/Shake',
                    'Wine',
                    'Fruit',
                    'Pizza',
                    'Beer',
                    'Sandwiches',
                    'Dessert',
                    'Short Orders',
                    'Cocktails',
                    'Hot',
                    'Chicken',
                    'Pork',
                    'Seafood',
                    'Noodles',
                    'Breakfast',
                    'Beef'
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
}
