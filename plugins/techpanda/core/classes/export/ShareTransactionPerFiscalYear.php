<?php

namespace Techpanda\Core\Classes\Export;

use Backend\Models\User;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Events\BeforeWriting;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Techpanda\Core\Classes\Helper;
use Techpanda\Core\Models\AccountHead;
use Techpanda\Core\Models\Association;
use Techpanda\Core\Models\MonthlySaving;
use Techpanda\Core\Models\Transaction;

class ShareTransactionPerFiscalYear implements FromArray, WithTitle, WithHeadings, WithEvents, ShouldAutoSize
{

    use Exportable, RegistersEventListeners;

    private $fiscalYear;
    private $tenant;
    public const userCells = 5;
    public const balanceCells = 1;
    public const cellsforEachMonth = 2;

    public function __construct($fiscalYear, $tenant)
    {
        $this->tenant = $tenant;
        $this->fiscalYear  = $fiscalYear;
    }

    public function headings(): array
    {

        //user cells
        for ($i = 1; $i <= self::userCells; $i++) {
            $user[] = ' ';
        }


        $months = Transaction::getMonthsByFiscalYear($this->fiscalYear);
        $monthCols = [];

        $startBalance = 'Share Up to ' . date("F-Y", strtotime("-1 months", strtotime('1-' . $months[0])));
        for ($i = 1; $i <= self::balanceCells; $i++) {
            $monthCols[] = $i == 1 ? $startBalance : ' ';
        }

        foreach ($months as $month) {

            $monthCols[] = $month;
            for ($i = 1; $i <= self::cellsforEachMonth - 1; $i++) {
                $monthCols[] = ' ';
            }
        }

        $endBalance = 'Share Up to ' . date("F-Y", strtotime('1-' . $months[count($months) - 1]));
        for ($i = 1; $i <= self::balanceCells; $i++) {
            $monthCols[] = $i == 1 ? $endBalance : ' ';
        }

        $heads = array_merge($user, $monthCols);

        //traceLog($heads);
        return $heads;
    }

    /**
     * @return Builder
     */
    public function array(): array
    {


        $firstRow = ['Sl No.', 'Member No.', 'Name', 'Designstion', 'Mobile'];

        for ($i = 0; $i < 14; $i++) {
            if (in_array($i, [0, 13])) {
                $firstRow[] = 'Share';
            } else {
                $firstRow[] = 'Share';
                $firstRow[] = 'Tnx Date';
            }
        }

        $data = $this->dataByFiscalYear($this->fiscalYear);

        array_unshift($data, $firstRow);

        return $data;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->fiscalYear;
    }

    public static function beforeExport(BeforeExport $event)
    {
        //
    }

    public static function beforeWriting(BeforeWriting $event)
    {
        //
    }

    public static function beforeSheet(BeforeSheet $event)
    {
        //
    }

    public static function afterSheet(AfterSheet $event)
    {
        self::formatHeaderRow($event);
    }

    public static function formatHeaderRow(AfterSheet $event)
    {


        $alfabets = range('A', 'Z');

        foreach (['A', 'B'] as $char) {

            foreach (range('A', 'Z') as $charchar)
                $alfabets[] = "$char" . "$charchar";
        }


        $cellsCountPerMonth = self::cellsforEachMonth;
        $from = self::userCells + self::balanceCells;

        $ranges = [];


        //for merging cells

        for ($i = 1; $i <= 12; $i++) {


            if ($i == 1) {

                //for user cells
                $ranges[] = $alfabets[0] . '1:' . $alfabets[self::userCells - 1] . '1';

                //for start balance
                $ranges[] = $alfabets[self::userCells] . '1:' . $alfabets[$from - 1] . '1';
            }

            //for months
            $from = $from + 1;
            $to = $from + $cellsCountPerMonth - 1;
            $ranges[] = $alfabets[$from - 1] . '1:' . $alfabets[$to - 1] . '1';
            $from = $to;

            //for end balance
            if ($i == 12) {
                $ranges[] = $alfabets[$from] . '1:' . $alfabets[$from + self::balanceCells - 1] . '1';
            }
        }


        $styleArray = [
            'font' => [
                'name' => 'Arial',
                'bold' => true,
                'italic' => false,
                'color' => [
                    'rgb' => '000000'
                ]
            ],
            'backgroundColor' => [
                'rgb' => '000000'
            ],

            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => false,
            ],
            'quotePrefix'    => true
        ];

        //traceLog($ranges);

        foreach ($ranges as $range) {
            list($from, $to) = explode(":", $range);

            $value = $event->sheet->getDelegate()->getCell("$from")->getValue();
            $event->sheet->getDelegate()->mergeCells("$from:$to")->getCell("$from")->setValue($value);
        }



        // //set style
        $cellRange = 'A1:CF1'; // All headers
        $event->sheet->getDelegate()->getStyle($cellRange)->applyFromArray($styleArray);
    }

    public function dataByFiscalYear($fiscalYear)
    {
        $data = [];
        list($from, $to) = explode('-', $fiscalYear);
        $members = $this->tenant->members;
        $monthSaving = 4000;
        $transaction = new Transaction();

        $fiscalYearMonths = Transaction::getMonthsByFiscalYear($fiscalYear);

        //all share transactions
        $allshareTnx = AccountHead::allShareTransactions();
        //['Sl No.', 'Member No.', 'Name', 'Designstion', 'Mobile'];
        $i = 1;
        foreach ($members as $member) {

            //months value
            $months = $transaction->getPaidMonthsByFiscalYear($fiscalYear, $member->id, [Transaction::STATUS_PAID]);



            //filter out close members
            if (!$member->is_activated && empty($months))
                continue;

            $row = [
                $i,
                $member->login,
                $member->full_name,
                $member->designation,
                $member->mobile
            ];

            // fiscal year opening balance
            $firstMonth = date('Y-m-d', mktime(0, 0, 0, 6, 30, $from));
            $headTotals = AccountHead::headsAmount($member->id, null, $firstMonth);

            $row[] = $headTotals[AccountHead::getShareHeadName()];


            $userShareTnx = $allshareTnx->where('user_id', $member->id)->keyBy(function ($item) {
                return $item->year . '_' . $item->month;
            })->all();

            foreach ($fiscalYearMonths as $my) {

                list($month, $year) = explode('-', $my);

                if (isset($userShareTnx[$year . '_' . $month]) && $tnx = $userShareTnx[$year . '_' . $month]) {
                    $row[] = $tnx->fee * $tnx->quantity;
                    $row[] = date("d-m-Y", strtotime($tnx->tnx_date));
                } else {
                    $row[] = 0;
                    $row[] = 0;
                }
            }

            //fiscal year ending balance
            $lastMonth = date('Y-m-d', mktime(0, 0, 0, 6, 30, $to));
            $headTotals = AccountHead::headsAmount($member->id, null, $lastMonth);

            $row[] = $headTotals[AccountHead::getShareHeadName()];

            $i++;

            $data[] = $row;
        }

        return $data;
    }
}
