<?php

namespace Techpanda\Core\Models;

use Model;

use function Matrix\trace;

/**
 * Model
 */
class MonthlySaving extends Model
{
    use \October\Rain\Database\Traits\Validation;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'techpanda_core_monthly_savings';

    /**
     * @var array Validation rules
     */
    public $rules = [

        'transaction_id' => 'required',
        'user_id' => 'required'
    ];

    public $belongsTo = [

        'user' => 'Backend\Models\User',
        'transaction' => 'Techpanda\Core\Models\Transaction'
    ];

    public static function getTotalSavings()
    {
        $savings = MonthlySaving::with(['user' => function ($q) {
            $q->select('id');
        }, 'transaction' => function ($q) {
            $q->select('id', 'status');
        }])
            ->whereHas('transaction', function ($q) {
                $q->where('status', Transaction::STATUS_PAID);
            })
            ->get()
            ->toArray();

        $requestDate = '2021-03-31';
        $userSavings = collect($savings)
            //->keyBy('month')
            ->filter(function ($item) {
                traceLog($item);
                return $item['user']['id'] == 90;
            })
            // ->filter(function ($item) use ($requestDate) {

            //     $mkTime = mktime(0, 0, 0, date("m", strtotime($item['month'])), 1, $item['year']);
            //     $month = date('Y-m-t', $mkTime);
            //     return strtotime($month) <= strtotime($requestDate);
            // })
            ->toArray();

        traceLog($userSavings);

        return $savings;
    }
}
