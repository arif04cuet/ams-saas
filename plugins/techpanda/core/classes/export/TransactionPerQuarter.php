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

use function Matrix\trace;

class TransactionPerQuarter implements FromArray, WithTitle, WithHeadings, WithEvents, ShouldAutoSize
{

    use Exportable, RegistersEventListeners;

    private $fiscalYear;
    private $quarter;
    private $tenant;
    private $fiscalMonths;
    public const userCells = 5;
    public const balanceCells = 1;
    public const cellsforEachMonth = 2;

    public function __construct($tenant, $fiscalYear, $quarter)
    {
        $this->tenant = $tenant;
        $this->fiscalYear  = $fiscalYear;
        $this->quarter  = $quarter;
        $this->fiscalMonths = Transaction::getMonthsByFiscalYear($fiscalYear);
    }

    public function getQuarterMonths($fyMonths, $quarter)
    {
        $monthCount = 3;
        $offset = ($quarter - 1) * $monthCount;

        $quarterMonths = array_slice($fyMonths, $offset, $monthCount);

        return $quarterMonths;
    }
    public function headings(): array
    {

        //user cells
        for ($i = 1; $i <= self::userCells; $i++) {
            $user[] = ' ';
        }


        $months = $this->getQuarterMonths($this->fiscalMonths, $this->quarter);

        $monthCols = [];

        $startBalance = 'Savings Up to ' . date("F-Y", strtotime("-1 months", strtotime('1-' . $months[0])));
        for ($i = 1; $i <= self::balanceCells; $i++) {
            $monthCols[] = $i == 1 ? $startBalance : ' ';
        }

        foreach ($months as $month) {

            $monthCols[] = $month;
            for ($i = 1; $i <= self::cellsforEachMonth - 1; $i++) {
                $monthCols[] = ' ';
            }
        }

        $endBalance = 'Savings Up to ' . date("F-Y", strtotime('1-' . $months[count($months) - 1]));
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

        $quarterMonths = $this->getQuarterMonths($this->fiscalMonths, $this->quarter);

        $firstRow = ['Sl No.', 'Member No.', 'Name', 'Designstion', 'Mobile'];

        for ($i = 0; $i <= count($quarterMonths); $i++) {
            if ($i == 0) {
                $firstRow[] = '';
            } else {
                $firstRow[] = 'Savings';
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
        $cellRange = 'A2:CF2'; // All headers
        $event->sheet->getDelegate()->getStyle($cellRange)->applyFromArray($styleArray);
    }

    public function getHeadTotals($member)
    {
        list($from, $to) = explode('-', $this->fiscalYear);

        // fiscal year opening balance
        $day = 30;
        $month = 0;
        $year = 0;
        switch ($this->quarter) {
            case 1:
                $month = 6;
                $year = $from;
                break;

            case 2:
                $month = 9;
                $year = $from;
                break;

            case 3:
                $day = 31;
                $month = 12;
                $year = $from;
                break;

            case 4:
                $day = 31;
                $month = 3;
                $year = $to;
                break;
        }

        $firstMonth = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));
        $headTotals = AccountHead::headsAmount($member->id, null, $year);

        return $headTotals;
    }
    public function dataByFiscalYear($fiscalYear)
    {
        $data = [];
        list($from, $to) = explode('-', $fiscalYear);
        $members = $this->tenant->members;
        $monthSaving = Transaction::PER_MONTH_AMOUNT;
        $transaction = new Transaction();

        $quarterMonths = $this->getQuarterMonths($this->fiscalMonths, $this->quarter);
        $allSavingsMonth = MonthlySaving::getTotalSavings();

        //['Sl No.', 'Member No.', 'Name', 'Designstion', 'Mobile'];
        $i = 1;
        foreach ($members as $member) {

            //months value
            $months = $transaction->getPaidMonthsByFiscalYear($fiscalYear, $member->id, [Transaction::STATUS_PAID]);

            //filter out close members
            $dipositedMonths = collect($quarterMonths)->filter(function ($item) use ($months) {
                $parts = explode('-', $item);
                return in_array($parts[0], $months);
            })->toArray();

            if (!$member->is_activated && empty($dipositedMonths))
                continue;

            $row = [
                $i,
                $member->login,
                $member->full_name,
                $member->designation,
                $member->mobile
            ];


            // excel upto month balance
            $fyMy = explode('-', $quarterMonths[0]);
            $prevMonth = date("m", strtotime($fyMy[0] . ' last month'));
            $year = $this->quarter == 3 ? $from : $fyMy[1];

            $excelUpToMonth = date('Y-m-t', mktime(0, 0, 0, $prevMonth, 1, $year));

            $userSavings = MonthlySaving::getTotalSavingsByUser($member, $allSavingsMonth, $excelUpToMonth);
            $userSavingList = MonthlySaving::getTotalSavingsByUser($member, $allSavingsMonth);


            $row[] = $userSavings['amount'];

            foreach ($quarterMonths as $my) {

                list($month, $year) = explode('-', $my);

                $monthly = [
                    'savings' => 0,
                    'share' => 0
                ];

                if (in_array($month, $months)) {
                    $monthly['savings'] = $monthSaving;
                    $monthly['share'] = date("d-m-Y", strtotime($userSavingList['items'][$my]['transaction']['tnx_date']));
                }

                $row[] = $monthly['savings'];
                $row[] = $monthly['share'];
            }

            //fiscal year ending balance
            $fyMy = explode('-', $quarterMonths[count($quarterMonths) - 1]);
            $qLastMonth = date("m", strtotime($fyMy[0]));
            $year = $fyMy[1];
            $excelLastMonth = date('Y-m-t', mktime(0, 0, 0, $qLastMonth, 1, $year));

            $userSavings = MonthlySaving::getTotalSavingsByUser($member, $allSavingsMonth, $excelLastMonth);

            $row[] = $userSavings['amount'];

            $i++;

            $data[] = $row;
        }

        //traceLog($data);
        return $data;
    }
}
