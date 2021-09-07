<?php

namespace Techpanda\Core\Classes\Export;


use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Techpanda\Core\Classes\Helper;
use Techpanda\Core\Models\Association;
use Techpanda\Core\Models\Transaction;

use function Matrix\trace;

class TransactionExport implements WithMultipleSheets
{
    use Exportable;

    public $tenant;

    public function __construct($tenantId)
    {
        $this->tenant = Association::with('members')->find($tenantId);
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        $fiscalYears = Transaction::select(['title'])->groupBy('title')->orderBy('title', 'desc')->get()->pluck('title');

        foreach ($fiscalYears as $fiscalYear) {
            $sheets[] = new TransactionPerFiscalYear($fiscalYear, $this->tenant);
        }

        // others head sheet
        $sheets[] = new OthersHeadAmount($this->tenant);

        return $sheets;
    }
}
