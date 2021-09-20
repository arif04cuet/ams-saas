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
        $savings = MonthlySaving::with([

            'user' => function ($q) {
                $q->select('id');
            },
            'transaction' => function ($q) {
                $q->select('id', 'status');
            }
        ])->whereHas('transaction', function ($q) {
            $q->where('status', Transaction::STATUS_PAID);
        })
            ->get()
            ->groupBy('user.id')
            ->toArray();


        return $savings;
    }

    public static function getTotalSavingsByUser($user, $items, $toDate = null)
    {
        $list = collect($items)->filter(function ($items, $key) use ($user) {
            return $key == $user->id;
        })->first();

        if (!is_null($toDate)) {
            $list = collect($list)->filter(function ($item) use ($toDate) {
                $mkTime = mktime(0, 0, 0, date("m", strtotime($item['month'])), 1, $item['year']);
                $month = date("Y-m-t", $mkTime);
                return strtotime($month) <= strtotime($toDate);
            });
        }

        //initial balance
        $initialBalance = 0;
        if ($initialBalanceData = $user->initial_balance) {
            $initialBalance = isset($initialBalanceData[0]['amount']) ? $initialBalanceData[0]['amount'] : 0;
        }

        if ($user->id == 90)
            traceLog($list->count());

        $total = $initialBalance;
        $total += $list->count() ? $list->count() * Transaction::getPerMonthSaving() : 0;

        return [
            'amount' => $total,
            'items' => $list->keyBy('month')->toArray()
        ];
    }
}
