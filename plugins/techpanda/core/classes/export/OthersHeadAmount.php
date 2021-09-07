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
use Techpanda\Core\Models\Transaction;

class OthersHeadAmount implements FromArray, WithTitle, WithHeadings, WithEvents, ShouldAutoSize
{

    use Exportable, RegistersEventListeners;

    private $tenant;

    public function __construct($tenant)
    {
        $this->tenant = $tenant;
    }

    public function headings(): array
    {

        $userHead = ['Sl No.', 'Member No.', 'Name', 'Designstion', 'Mobile'];
        $accountHeads = AccountHead::get()->pluck('name')->toArray();

        $heads = array_merge($userHead, $accountHeads);

        return $heads;
    }

    /**
     * @return Builder
     */
    public function array(): array
    {
        $data = [];
        $i = 1;

        $members = $this->tenant->members;

        foreach ($members as $member) {

            $row = [
                $i,
                $member->login,
                $member->full_name,
                $member->designation,
                $member->mobile,
            ];

            $row = array_merge($row, $this->headData($member));

            $data[] = $row;
        }

        return $data;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Aggregated Heads Values';
    }

    public function headData($member)
    {
        $headData = AccountHead::headsAmount($member->id);

        return array_values($headData);
    }
}
