<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\RestController;
use App\Models\Accounting\Account;
use App\Models\Accounting\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class TransactionController extends RestController
{
    protected static $model = Transaction::class;
    protected static $with = ['supplier:id,tin,name'];
    protected static $validations = [
        'date' => 'required|date',
        'from_account_id' => 'required|exists:.eden.accounts,id',
        'to_account_id' => 'required|exists:.eden.accounts,id',
        'note' => 'required|string',
        'amount' => 'required|numeric',
        'vat' => 'nullable|numeric',
        'tin' => 'nullable|string',
        'official_receipt' => 'nullable|string',
        'supplier_id' => 'nullable|exists:.eden.suppliers,id'

    ];
    protected static $indexColumns = ['id', 'date', 'note', 'amount', 'updated_at'];
    protected static $orderBy = ['date', 'id'];

    public function show($id)
    {
        $result = parent::show($id);
        $imageUrls = [];
        foreach (glob(config('eden.eden_receipt_dir') . "/{$id}_*") as $filename) {
            $imageUrls[] = "/api/accounting/transactions/image/" . basename($filename);
        }
        $result['images'] = $imageUrls;
        return $result;
    }

    public function store()
    {
        $transaction = Request::validate(static::$validations);
        $fromAccount = Account::find($transaction['from_account_id']);
        $toAccount = Account::find($transaction['to_account_id']);

        /* Create offset for number series */
        $offset = date('Y', strtotime($transaction['date']));
        if ($transaction['from_account_id'] === 62 || $transaction['to_account_id'] === 62) {
            $offset .= 9;
        } elseif ($fromAccount->type == 'I') {
            $offset .= 1;
        } elseif ($toAccount->type == 'C') {
            $offset .= 0;
        } else {
            $offset .= 8;
        }
        $id = DB::selectOne(
            "SELECT MAX(id) AS id
            FROM eden.transactions
            WHERE id BETWEEN {$offset}00000 AND {$offset}99999"
        )->id;
        if (!isset($id)) {
            $id = $offset . '00001';
        } else {
            $id++;
        }
        $transaction['id'] = $id;

        static::$model::create($transaction);

        /* Index row */
        $query = static::$model::with(static::$with)
            ->select(static::$indexColumns);
        $this->filter($query, [$this->primaryKey => $id]);
        return $query->first();
    }


    public function uploadImage()
    {
        $data = Request::validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
            'id' => 'required|numeric'
        ]);
        $fileNumber = 0;
        do {
            $fileNumber++;
            $imageName = "{$data['id']}_{$fileNumber}.{$data['image']->extension()}";
        } while (file_exists(config('eden.eden_receipt_dir') . "/$imageName"));
        $data['image']->move(config('eden.eden_receipt_dir'), $imageName);
    }


    public function showImage($id)
    {
        $file = config('eden.eden_receipt_dir') . "/$id";
        $c = file_get_contents($file);
        $size = filesize($file);
        header('Content-Type: ' . mime_content_type($file));
        header("Content-length: $size");
        echo $c;
        exit;
    }
}
