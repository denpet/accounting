<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\RestController;
use App\Models\Accounting\Transaction;
use Illuminate\Support\Facades\Request;

class TransactionController extends RestController
{
    protected static $model = Transaction::class;
    protected static $validations = [
        'date' => 'required|date',
        'from_account_id' => 'required|exists:.eden.accounts,id',
        'to_account_id' => 'required|exists:.eden.accounts,id',
        'note' => 'required|string',
        'amount' => 'required|numeric',
        'vat' => 'required|numeric',
        'tin' => 'nullable|string',
        'official_receipt' => 'nullable|string'
    ];
    protected static $indexColumns = ['id', 'date', 'note', 'amount'];
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


    public function uploadImage($id)
    {
        $data = Request::validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        $fileNumber = 0;
        do {
            $fileNumber++;
            $imageName = "{$id}_{$fileNumber}.{$data['image']->extension()}";
        } while (file_exists(config('eden.eden_receipt_dir') . "/$imageName"));

        $data['image']->move(config('eden.eden_receipt_dir'), $imageName);

        /* Store $imageName name in DATABASE from HERE */

        return back()
            ->with('success', 'You have successfully upload image.')
            ->with('image', $imageName);
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
