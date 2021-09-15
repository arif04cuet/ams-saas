<?php

namespace Techpanda\Core\Classes\Export;


use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Techpanda\Core\Classes\Helper;
use Techpanda\Core\Models\Association;
use Techpanda\Core\Models\Transaction;

use function Matrix\trace;

class QTransactionExport implements WithMultipleSheets
{
    use Exportable;

    public $tenant;
    public $fiscalYear;
    public $quarter;

    public function __construct($tenantId, $fiscalYear, $quarter)
    {
        $this->tenant = Association::with('members')->find($tenantId);
        $this->fiscalYear = $fiscalYear;
        $this->quarter = $quarter;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        $fiscalYears = Transaction::select(['title'])
            ->groupBy('title')
            ->orderBy('title', 'desc')
            ->get()
            ->pluck('title')
            ->toArray();

        if (in_array($this->fiscalYear, $fiscalYears))
            $sheets[] = new TransactionPerQuarter($this->tenant, $this->fiscalYear, $this->quarter);

        return $sheets;
    }
}
