<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\RestController;
use App\Models\Payroll\TimeRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TimeRecordController extends RestController
{
    protected static $model = TimeRecord::class;
    protected static $validations = [
        'id' => 'required|numeric',
        'employee_id' => 'required|exists:.eden.employees,id',
        'biometric_timestamp' => 'required|datetime',
        'biometric_status' => 'required|in:1,3,15',
        'adjusted_timestamp' => 'required|datetime',
        'hide' => 'required|boolean',
    ];
    protected static $indexColumns = ['id', 'biometric_timestamp', 'biometric_status', 'adjusted_timestamp', 'hide', 'employee_id'];
    protected static $with = ['employee:id,name', 'biometricStatus:id,name'];
    protected static $orderBy = ['employee_id', 'biometric_timestamp'];

    public function weekOptions()
    {
        $range = DB::selectOne(
            "SELECT MIN(adjusted_timestamp) AS first_date, 
                MAX(adjusted_timestamp) AS last_date
            FROM eden.time_records"
        );

        /* Get first day of week for min and max */
        $firstWeek = strtotime(Date('Y-m-d', strtotime('Last Saturday', strtotime($range->first_date))));
        $today = Date('w', strtotime($range->last_date));
        if ($today != 6) {
            $lastWeek = strtotime(Date('Y-m-d', strtotime('Next Saturday', strtotime($range->last_date))));
        } else {
            $lastWeek = strtotime($range->last_date);
        }
        $data = [];
        for ($date = $lastWeek; $date >= $firstWeek; $date -= 7 * 86400) {
            $data[] = [
                'value' => Date('Y-m-d', $date),
                'label' => Date('Y-m-d', $date) . " - " . Date('Y-m-d', strtotime('Next Friday', $date))
            ];
        }
        return $data;
    }

    public function index()
    {
        $weekOf = Request::input('week', false);
        $from = "$weekOf 00:00:00";
        $to = Date('Y-m-d', strtotime('Next Friday', strtotime($weekOf))) . " 23:59:59";
        return ['data' => DB::select(
            "SELECT DISTINCT employee_id,
                e.name,
                :week as week
            FROM eden.time_records tr
            JOIN eden.employees e ON e.id=tr.employee_id
            WHERE adjusted_timestamp BETWEEN :from AND :to
            ORDER BY e.name",
            ['from' => $from, 'to' => $to, 'week' => $weekOf]
        )];
    }

    public function showEmployeeWeek($employeeId, $weekOf)
    {
        $from = "$weekOf 00:00:00";
        $to = Date('Y-m-d', strtotime('Next Friday', strtotime($weekOf))) . " 23:59:59";
        return DB::select(
            "SELECT tr.*,
                bs.name AS biometric_status_name
            FROM eden.time_records tr
            LEFT JOIN eden.biometric_statuses bs ON bs.id = tr.biometric_status
            WHERE employee_id=:employeeId
                AND adjusted_timestamp BETWEEN :from AND :to
            ORDER BY adjusted_timestamp",
            ['employeeId' => $employeeId, 'from' => $from, 'to' => $to]
        );
    }

    public function store()
    {
        $timeRecords = Request::all();
        foreach ($timeRecords as $timeRecord) {
            if ($timeRecord['id']) {
                DB::update(
                    "UPDATE eden.time_records
                    SET adjusted_timestamp=:timestamp,
                        hide=:hide
                    WHERE id=:id",
                    ['timestamp' => $timeRecord['adjusted_timestamp'], 'hide' => $timeRecord['hide'], 'id' => $timeRecord['id']]
                );
            } else {
                DB::insert(
                    "INSERT INTO eden.time_records (employee_id, adjusted_timestamp, hide)
                    VALUES (:employee_id, :adjusted_timestamp, :hide)",
                    [
                        'employee_id' => $timeRecord['employee_id'],
                        'adjusted_timestamp' => $timeRecord['adjusted_timestamp'],
                        'hide' => $timeRecord['hide']
                    ]
                );
            }
        }
    }

    public function payroll($weekOf)
    {
        /* Select all record within range */
        $from = "$weekOf 00:00:00";
        $to = Date('Y-m-d', strtotime('Next Friday', strtotime($weekOf))) . " 23:59:59";

        $result = DB::select(
            "SELECT *
            FROM eden.time_records tr
            JOIN eden.employees e ON e.id=tr.employee_id
            WHERE e.active != 0
	            AND adjusted_timestamp BETWEEN :from AND :to
            ORDER BY e.active, e.name, e.id, adjusted_timestamp",
            ['from' => $from, 'to' => $to]
        );

        $prevRow = $result[0];
        $time = 0.0;
        $messages = [];
        $payroll = [];
        $status = 0;
        foreach ($result as $key => $row) {
            if ($row->hide) continue;
            if ($row->employee_id != $prevRow->employee_id) {
                $payroll[] = array(
                    'name' => $prevRow->name,
                    'rate' => $prevRow->rate,
                    'hours' => ($time / 3600),
                    'active' => $prevRow->active
                );
                $status = 1;
                $prevRow = $row;
                $time = 0.0;
            }
            $row->date = Date('Y-m-d', strtotime($row->adjusted_timestamp));
            $row->time = Date('H:i', strtotime($row->adjusted_timestamp));

            /*
             * 0 = Unknown
             * 1 = Check-in
             * 2 = Check-out
             */
            $texts = [];
            $texts[1] = "Check-in";
            $texts[2] = "Check-out";
            if (($status == 2) && $key > 0) {
                $time += strtotime($row->adjusted_timestamp) - strtotime($result[$key - 1]->adjusted_timestamp);
                $status = 1;
            } else {
                $status = 2;
            }
        }

        $payroll[] = array(
            'name' => $prevRow->name,
            'rate' => $prevRow->rate,
            'hours' => ($time / 3600),
            'active' => $prevRow->active
        );

        /* Out put an Excel file if no errors exists */
        if (count($messages) == 0) {
            $styleArray = array(
                'borders' => array(
                    'outline' => array(
                        'style' => Border::BORDER_THIN,
                        'color' => array(
                            'argb' => '00000000'
                        )
                    )
                )
            );

            $objPHPExcel = new Spreadsheet();
            $objPHPExcel->getProperties()
                ->setCreator("Eden Resort")
                ->setLastModifiedBy("Eden Resort")
                ->setTitle("Payroll")
                ->setSubject("Payroll")
                ->setDescription("Payroll for Eden Resort.")
                ->setKeywords("payroll")
                ->setCategory("Payroll");

            /* Create employee sheet */
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()
                ->getPageSetup()
                ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
            $objPHPExcel->getActiveSheet()->setTitle('PAYROLL');
            $objPHPExcel->getActiveSheet()
                ->getColumnDimension('A')
                ->setWidth(25);
            $objPHPExcel->getActiveSheet()
                ->getColumnDimension('B')
                ->setWidth(15);
            $objPHPExcel->getActiveSheet()
                ->getColumnDimension('C')
                ->setWidth(10);
            $objPHPExcel->getActiveSheet()
                ->getColumnDimension('D')
                ->setWidth(10);
            $objPHPExcel->getActiveSheet()
                ->getColumnDimension('E')
                ->setWidth(10);
            $objPHPExcel->getActiveSheet()
                ->getColumnDimension('F')
                ->setWidth(10);
            $objPHPExcel->getActiveSheet()
                ->getColumnDimension('G')
                ->setWidth(17);
            $objPHPExcel->getActiveSheet()
                ->getColumnDimension('H')
                ->setWidth(25);
            $objPHPExcel->getActiveSheet()
                ->setCellValue('A1', "P A Y R O L L")
                ->setCellValue('A2', "For the period of $weekOf to " . Date('Y-m-d', strtotime($weekOf) + 604799))
                ->setCellValue('A3', "WE HEREBY ACKKNOWLEDGE to have received from CEBU GARDEN OF EDEN RESORT, INC,   LILOAN, SANTANDER, CEBU")
                ->setCellValue('A4', "the sum specified opposite our respective names, as full compensation for services rendered.")
                ->setCellValue('A5', "NAME OF EMPLOYEE")
                ->setCellValue('B5', "HOURS OF WORK")
                ->setCellValue('C5', "RATE")
                ->setCellValue('D5', "AMOUNT")
                ->setCellValue('E5', "S.S.S.")
                ->setCellValue('F5', "PhilHealth")
                ->setCellValue('G5', "NET AMOUNT PAID")
                ->setCellValue('H5', "SIGNATURE");

            /* Merge header */
            $objPHPExcel->getActiveSheet()->mergeCells("A1:H1");
            $objPHPExcel->getActiveSheet()->mergeCells("A2:H2");
            $objPHPExcel->getActiveSheet()->mergeCells("A3:H3");
            $objPHPExcel->getActiveSheet()->mergeCells("A4:H4");

            /* Set font properties */
            $objPHPExcel->getActiveSheet()
                ->getStyle("A1")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()
                ->getStyle("A1")
                ->getFont()
                ->setBold(true)
                ->setSize(24);

            $objPHPExcel->getActiveSheet()
                ->getStyle("A5")
                ->getFont()
                ->setBold(true);
            $objPHPExcel->getActiveSheet()
                ->getStyle("B5")
                ->getFont()
                ->setBold(true);
            $objPHPExcel->getActiveSheet()
                ->getStyle("C5")
                ->getFont()
                ->setBold(true);
            $objPHPExcel->getActiveSheet()
                ->getStyle("D5")
                ->getFont()
                ->setBold(true);
            $objPHPExcel->getActiveSheet()
                ->getStyle("E5")
                ->getFont()
                ->setBold(true);
            $objPHPExcel->getActiveSheet()
                ->getStyle("F5")
                ->getFont()
                ->setBold(true);
            $objPHPExcel->getActiveSheet()
                ->getStyle("G5")
                ->getFont()
                ->setBold(true);
            $objPHPExcel->getActiveSheet()
                ->getStyle("H5")
                ->getFont()
                ->setBold(true);

            /* Set borders */
            $objPHPExcel->getActiveSheet()
                ->getStyle('A5:A5')
                ->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()
                ->getStyle('B5:B5')
                ->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()
                ->getStyle('C5:C5')
                ->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()
                ->getStyle('D5:D5')
                ->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()
                ->getStyle('E5:E5')
                ->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()
                ->getStyle('F5:F5')
                ->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()
                ->getStyle('G5:G5')
                ->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()
                ->getStyle('H5:H5')
                ->applyFromArray($styleArray);

            /* Set height */
            $objPHPExcel->getActiveSheet()
                ->getRowDimension(1)
                ->setRowHeight(25);
            for ($row = 2; $row < 6; $row++) {
                $objPHPExcel->getActiveSheet()
                    ->getRowDimension($row)
                    ->setRowHeight(20);
            }

            /* Create contractor sheet */
            $objPHPExcel->createSheet(1);
            $objPHPExcel->setActiveSheetIndex(1);
            $objPHPExcel->getActiveSheet()
                ->getPageSetup()
                ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
            $objPHPExcel->getActiveSheet()->setTitle('CONTRACTOR PAYOUT');
            $objPHPExcel->getActiveSheet()
                ->getColumnDimension('A')
                ->setWidth(25);
            $objPHPExcel->getActiveSheet()
                ->getColumnDimension('B')
                ->setWidth(15);
            $objPHPExcel->getActiveSheet()
                ->getColumnDimension('C')
                ->setWidth(10);
            $objPHPExcel->getActiveSheet()
                ->getColumnDimension('D')
                ->setWidth(17);
            $objPHPExcel->getActiveSheet()
                ->getColumnDimension('E')
                ->setWidth(25);
            $objPHPExcel->getActiveSheet()
                ->setCellValue('A1', "C O N T R A C T O R   P A Y O U T")
                ->setCellValue('A2', "For the period of $weekOf to " . Date('Y-m-d', strtotime($weekOf) + 604799))
                ->setCellValue('A3', "WE HEREBY ACKKNOWLEDGE to have received from CEBU GARDEN OF EDEN RESORT, INC,   LILOAN, SANTANDER, CEBU")
                ->setCellValue('A4', "the sum specified opposite our respective names, as full compensation for services rendered.")
                ->setCellValue('A5', "NAME OF CONTRACTOR")
                ->setCellValue('B5', "HOURS OF WORK")
                ->setCellValue('C5', "RATE")
                ->setCellValue('D5', "NET AMOUNT PAID")
                ->setCellValue('E5', "SIGNATURE");

            /* Merge header */
            $objPHPExcel->getActiveSheet()->mergeCells("A1:E1");
            $objPHPExcel->getActiveSheet()->mergeCells("A2:E2");
            $objPHPExcel->getActiveSheet()->mergeCells("A3:E3");
            $objPHPExcel->getActiveSheet()->mergeCells("A4:E4");

            /* Set font properties */
            $objPHPExcel->getActiveSheet()
                ->getStyle("A1")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()
                ->getStyle("A1")
                ->getFont()
                ->setBold(true)
                ->setSize(24);

            $objPHPExcel->getActiveSheet()
                ->getStyle("A5")
                ->getFont()
                ->setBold(true);
            $objPHPExcel->getActiveSheet()
                ->getStyle("B5")
                ->getFont()
                ->setBold(true);
            $objPHPExcel->getActiveSheet()
                ->getStyle("C5")
                ->getFont()
                ->setBold(true);
            $objPHPExcel->getActiveSheet()
                ->getStyle("D5")
                ->getFont()
                ->setBold(true);
            $objPHPExcel->getActiveSheet()
                ->getStyle("E5")
                ->getFont()
                ->setBold(true);

            /* Set borders */
            $objPHPExcel->getActiveSheet()
                ->getStyle('A5:A5')
                ->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()
                ->getStyle('B5:B5')
                ->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()
                ->getStyle('C5:C5')
                ->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()
                ->getStyle('D5:D5')
                ->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()
                ->getStyle('E5:E5')
                ->applyFromArray($styleArray);

            /* Set height */
            $objPHPExcel->getActiveSheet()
                ->getRowDimension(1)
                ->setRowHeight(25);
            for ($row = 2; $row < 6; $row++) {
                $objPHPExcel->getActiveSheet()
                    ->getRowDimension($row)
                    ->setRowHeight(20);
            }

            /* Set records */
            $payrollRow = 6;
            $contractorRow = 6;
            foreach ($payroll as $key => $record) {
                if ($record['active'] == 1) {
                    $row = $payrollRow++;
                    $objPHPExcel->setActiveSheetIndex(0);
                } else {
                    $row = $contractorRow++;
                    $objPHPExcel->setActiveSheetIndex(1);
                }
                $objPHPExcel->getActiveSheet()
                    ->getRowDimension($row)
                    ->setRowHeight(25);
                $objPHPExcel->getActiveSheet()->setCellValue("A$row", $record['name']);
                $objPHPExcel->getActiveSheet()
                    ->setCellValue("B$row", $record['hours'])
                    ->getStyle("B$row")
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                $objPHPExcel->getActiveSheet()
                    ->setCellValue("C$row", $record['rate'])
                    ->getStyle("C$row")
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                $objPHPExcel->getActiveSheet()
                    ->setCellValue("D$row", "=B$row*C$row")
                    ->getStyle("D$row")
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                $objPHPExcel->getActiveSheet()
                    ->getStyle("A$row:A$row")
                    ->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()
                    ->getStyle("B$row:B$row")
                    ->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()
                    ->getStyle("C$row:C$row")
                    ->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()
                    ->getStyle("D$row:D$row")
                    ->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()
                    ->getStyle("E$row:E$row")
                    ->applyFromArray($styleArray);
                if ($record['active'] == 1) {
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue("G$row", "=D$row-E$row-F$row")
                        ->getStyle("G$row")
                        ->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                    $objPHPExcel->getActiveSheet()
                        ->getStyle("G$row")
                        ->getFont()
                        ->setBold(true);
                    $objPHPExcel->getActiveSheet()
                        ->getStyle("F$row:F$row")
                        ->applyFromArray($styleArray);
                    $objPHPExcel->getActiveSheet()
                        ->getStyle("G$row:G$row")
                        ->applyFromArray($styleArray);
                    $objPHPExcel->getActiveSheet()
                        ->getStyle("H$row:H$row")
                        ->applyFromArray($styleArray);
                } else {
                    $objPHPExcel->getActiveSheet()
                        ->getStyle("D$row")
                        ->getFont()
                        ->setBold(true);
                }
            }

            /* Set footer of PAYROLL */
            $objPHPExcel->setActiveSheetIndex(0);
            $row = $payrollRow++;
            $objPHPExcel->getActiveSheet()->setCellValue("A$row", "WE HEREBY CERTIFY that WE have paid in cash to each employee whose");
            $objPHPExcel->getActiveSheet()->mergeCells("A$row:H$row");
            $objPHPExcel->getActiveSheet()
                ->getRowDimension($row)
                ->setRowHeight(20);
            $row = $payrollRow++;
            $objPHPExcel->getActiveSheet()->setCellValue("A$row", "name appears in the above payroll the amount set opposite his/her name.");
            $objPHPExcel->getActiveSheet()->mergeCells("A$row:H$row");
            $objPHPExcel->getActiveSheet()
                ->getRowDimension($row)
                ->setRowHeight(20);
            $row = $payrollRow++;
            $objPHPExcel->getActiveSheet()->setCellValue("A$row", "=CONCATENATE(\"The amount paid in this payroll is ₱\",TEXT(SUM(G6:G" . ($payrollRow - 4) . "),\"0,000.00\"),\" including their overtime pay.\")");
            $objPHPExcel->getActiveSheet()->mergeCells("A$row:H$row");
            $objPHPExcel->getActiveSheet()
                ->getRowDimension($row)
                ->setRowHeight(20);
            $row = $payrollRow++;
            $objPHPExcel->getActiveSheet()->setCellValue("A$row", Date("Y-m-d") . " _____________________________________________________");
            $objPHPExcel->getActiveSheet()->mergeCells("A$row:H$row");
            $objPHPExcel->getActiveSheet()
                ->getRowDimension($row)
                ->setRowHeight(20);
            $row = $payrollRow++;
            $objPHPExcel->getActiveSheet()->setCellValue("A$row", "(Manager)");
            $objPHPExcel->getActiveSheet()->mergeCells("A$row:H$row");
            $objPHPExcel->getActiveSheet()
                ->getRowDimension($row)
                ->setRowHeight(20);

            /* Set footer of CONTRACTOR PAYOUT */
            $objPHPExcel->setActiveSheetIndex(1);
            $row = $contractorRow++;
            $objPHPExcel->getActiveSheet()->setCellValue("A$row", "WE HEREBY CERTIFY that WE have paid in cash to each contractor whose");
            $objPHPExcel->getActiveSheet()->mergeCells("A$row:E$row");
            $objPHPExcel->getActiveSheet()
                ->getRowDimension($row)
                ->setRowHeight(20);
            $row = $contractorRow++;
            $objPHPExcel->getActiveSheet()->setCellValue("A$row", "name appears in the above payroll the amount set opposite his/her name.");
            $objPHPExcel->getActiveSheet()->mergeCells("A$row:E$row");
            $objPHPExcel->getActiveSheet()
                ->getRowDimension($row)
                ->setRowHeight(20);
            $row = $contractorRow++;
            $objPHPExcel->getActiveSheet()->setCellValue("A$row", "=CONCATENATE(\"The amount paid is ₱\",TEXT(SUM(D6:D" . ($contractorRow - 4) . "),\"0,000.00\"),\".\")");
            $objPHPExcel->getActiveSheet()->mergeCells("A$row:E$row");
            $objPHPExcel->getActiveSheet()
                ->getRowDimension($row)
                ->setRowHeight(20);
            $row = $contractorRow++;
            $objPHPExcel->getActiveSheet()->setCellValue("A$row", Date("Y-m-d") . " _____________________________________________________");
            $objPHPExcel->getActiveSheet()->mergeCells("A$row:E$row");
            $objPHPExcel->getActiveSheet()
                ->getRowDimension($row)
                ->setRowHeight(20);
            $row = $contractorRow++;
            $objPHPExcel->getActiveSheet()->setCellValue("A$row", "(Manager)");
            $objPHPExcel->getActiveSheet()->mergeCells("A$row:E$row");
            $objPHPExcel->getActiveSheet()
                ->getRowDimension($row)
                ->setRowHeight(20);

            $objPHPExcel->setActiveSheetIndex(0);

            /* Create output */
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="invoice_reconcile.xls"');
            $writer = new Xlsx($objPHPExcel);
            $writer->save("php://output");
            die;
        }
    }
}
