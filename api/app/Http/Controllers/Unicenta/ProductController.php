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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
            'transaction' => "required|numeric",
            'quantity' => "required|numeric",
            'amount' => "required|numeric",
            'reason' => "nullable|string",
            'date' => "string",
        ]);

        DB::insert(
            "INSERT INTO eden.purchases (transaction, date, product, units, amount, reason, user)
            VALUES (:transaction, :date, :product, :units, :amount, :reason, :user)",
            [
                'transaction' => $data['transaction'],
                'date' => $data['date'],
                'product' => $id,
                'units' => $data['quantity'],
                'amount' => $data['amount'],
                'reason' => $data['reason'] ?? null,
                'user' => Auth::user()->name
            ]
        );

        if ($id !== 'nostock') {
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
                'datenew' => $data['date'],
                'reason' => StockDiary::REASON_IN_PURCHASE,
                'location' => static::LOCATION,
                'product' => $id,
                'units' => $data['quantity'],
                'price' => $product->pricebuy,
                'appuser' => Auth::user()->name
            ]);
        }
    }

    function stockOptions()
    {
        return DB::connection('unicenta')->select(
            "SELECT p.id AS value,
                CONCAT(p.name, ' (', c.name, ')') AS label
            FROM products p
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

    function cycleCountExcel()
    {
        $category = Request::input("category", null);
        $where = [];
        $param = [];

        if ($category) {
            $where[] = "c.name=:category";
            $param['category'] = $category;
        } else {
            $where[] = "c.name in (
                    'Bar Stock',
                    'Beer',
                    'Canned / Bottled',
                    'Fruit',
                    'Intern products',
                    'Kitchen stock',
                    'Stock Items',
                    'Wine'
                )";
        }

        $where = $where ? "WHERE " . implode(" AND ", $where) : '';
        $products = DB::connection('unicenta')->select(
            "SELECT p.id,
                p.name,
                c.name AS category_name
            FROM products p
            JOIN categories c ON p.category = c.id
            $where
            ORDER BY c.name, p.name",
            $param
        );

        $objPHPExcel = new Spreadsheet();
        $objPHPExcel->getProperties()
            ->setCreator("Eden Resort")
            ->setLastModifiedBy("Eden Resort")
            ->setTitle("Cycle count")
            ->setSubject("Cycle count")
            ->setDescription("Cycle count for Eden Resort.")
            ->setKeywords("cycle count")
            ->setCategory("Cycle Count");

        /* Create employee sheet */
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()
            ->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
        $objPHPExcel->getActiveSheet()->setTitle('CYCLE COUNT');
        $objPHPExcel->getActiveSheet()
            ->getColumnDimension('A')
            ->setWidth(15);
        $objPHPExcel->getActiveSheet()
            ->getColumnDimension('B')
            ->setWidth(40);
        $objPHPExcel->getActiveSheet()
            ->getColumnDimension('C')
            ->setWidth(17);

        $row = 1;
        $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(30);
        $objPHPExcel->getActiveSheet()->setCellValue("A$row", "Cycle Count");
        $objPHPExcel->getActiveSheet()
            ->getStyle("A1")
            ->getFont()
            ->setBold(true)
            ->setSize(24);

        $row++;
        $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(30);
        $objPHPExcel->getActiveSheet()->setCellValue("A$row", "Category")
            ->setCellValue("B$row", "Product")
            ->setCellValue("C$row", "Quantity");
        $objPHPExcel->getActiveSheet()
            ->getStyle("$row")
            ->getFont()
            ->setBold(true);

        foreach ($products as $key => $product) {
            $row++;
            $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(30);
            $objPHPExcel->getActiveSheet()->setCellValue("A{$row}", $product->category_name);
            $objPHPExcel->getActiveSheet()->setCellValue("B{$row}", $product->name);
            $objPHPExcel->getActiveSheet()->setCellValue("C{$row}", '___________________');
        }
        /* Create output */

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="cycle_count.xls"');
        $writer = new Xlsx($objPHPExcel);
        $writer->save("php://output");
        die;
    }

    function cycleCountIndex()
    {
        return DB::connection('unicenta')->select(
            "SELECT p.id,
                p.name,
                c.name AS category_name
            FROM products p
            JOIN categories c ON p.category = c.id
            WHERE c.name in (
                    'Bar Stock',
                    'Beer',
                    'Canned / Bottled',
                    'Fruit',
                    'Intern products',
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
