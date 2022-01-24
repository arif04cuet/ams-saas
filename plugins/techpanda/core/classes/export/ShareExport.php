<?php

namespace Techpanda\Core\Classes\Export;


use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Techpanda\Core\Classes\Helper;
use Techpanda\Core\Models\Association;
use Techpanda\Core\Models\Transaction;

class ShareExport implements WithMultipleSheets
{
    use Exportable;

    public $tenant;

    public function __construct($tenantId)
    {
        $this->tenant = Association::with(['members' => function ($q) {
            $q->whereIn('role_id', [1, 2]);
        }])->find($tenantId);
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        $fiscalYears = Transaction::select(['title'])->groupBy('title')->orderBy('title', 'desc')->get()->pluck('title');

        foreach ($fiscalYears as $fiscalYear) {
            $sheets[] = new ShareTransactionPerFiscalYear($fiscalYear, $this->tenant);
        }

        return $sheets;
    }
}
