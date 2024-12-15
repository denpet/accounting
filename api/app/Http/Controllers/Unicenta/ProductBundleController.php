<?php

namespace App\Http\Controllers\Unicenta;

use App\Http\Controllers\RestController;
use App\Models\Unicenta\ProductBundle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class ProductBundleController extends RestController
{
    protected static $model = ProductBundle::class;
    protected static $validations = [
        'id' => 'required|string',
        'product' => 'required|exists:.unicentaopos.products,id',
        'product_bundle' => 'required|exists:.unicentaopos.products,id',
        'quantity' => 'required|numeric',
    ];

    protected static $indexColumns = ['id', 'product_bundle'];
    protected static $orderBy = ['product_bundle'];
    protected static $optionColumn = 'product_bundle';

    public function index()
    {
        $product = Request::input('product', false);
        $params = [];
        $where = [];
        if ($product) {
            $where[] = "product=:product";
            $params['product'] = $product;
        }
        $where = $where ? "WHERE " . implode(" AND ", $where) : '';
        return ['data' => DB::connection('unicenta')->select(
            "SELECT pb.id,
                product,
                product_bundle,
                quantity
            FROM products_bundle pb
            JOIN products p ON pb.product_bundle=p.id
            $where
            ORDER BY p.name",
            $params
        )];
    }
}
