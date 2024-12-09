<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\RestController;
use App\Models\Accounting\Supplier;
use Illuminate\Support\Facades\DB;

class SupplierController extends RestController
{
    protected static $model = Supplier::class;
    protected static $validations = [
        'tin' => 'nullable|numeric',
        'name' => 'required|string',
        'address1' => 'nullable|string',
        'address2' => 'nullable|string',
        'postal_code' => 'nullable|string',
        'city varchar' => 'nullable|string',
        'province varchar' => 'nullable|string',
        'phone_number' => 'nullable|string',
    ];
    protected static $indexColumns = ['id', 'tin', 'name'];
    protected static $orderBy = ['name'];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $word = request()->input('word') . '%';
        return DB::select(
            "SELECT id,
                tin,
                name
            FROM eden.suppliers
            WHERE tin LIKE :word OR name LIKE :word2",
            ['word' => $word, 'word2' => $word]
        );
    }
}
