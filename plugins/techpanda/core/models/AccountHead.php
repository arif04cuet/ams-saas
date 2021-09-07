<?php

namespace Techpanda\Core\Models;

use Backend;
use Backend\Models\User;
use BackendAuth;
use Illuminate\Support\Facades\DB;
use Model;
use Techpanda\Core\Traits\Tenant;

/**
 * Model
 */
class AccountHead extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\Sortable;
    use Tenant;
    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'techpanda_core_account_heads';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required',
        'code' => 'required|unique:techpanda_core_account_heads'
    ];

    public $hasMany = [
        'headfees' => [
            'Techpanda\Core\Models\HeadFee',
            'key' => 'head_id'
        ]
    ];

    public static function getShareCount($users = [], $from = null, $to = null, $includeInitialValues = true)
    {

        $list = [];

        $heads = DB::table("techpanda_core_account_heads as ah")
            ->join('techpanda_core_head_fees as hf', 'ah.id', '=', 'hf.head_id')
            ->join('techpanda_core_transaction_head as th', 'th.headfee_id', '=', 'hf.id')
            ->join('techpanda_core_transactions as t', 't.id', '=', 'th.transaction_id')
            ->select([DB::raw("sum(th.total) as total_share"), 'ah.code'])
            ->groupBy('ah.code')
            ->where("t.status", "paid");

        //filter users
        if (!empty($users) and !is_array($users)) {
            $users = [$users];
        }

        if (!empty($users) and is_array($users)) {
            $heads->whereIn('t.user_id', $users);
        }

        if (!empty($from))
            $heads->whereDate('t.tnx_date', '>=', $from);

        if (!empty($to))
            $heads->whereDate('t.tnx_date', '<=', $to);

        $heads->where("ah.code", 'share');

        $heads = $heads->get()->first();


        $share = 0;

        //initial balance
        if ($includeInitialValues) {
            if ($initialBalanceArray = BackendAuth::getUser()->initial_balance) {
                //traceLog($initialBalanceArray);
                $initialBalance = [];
                foreach ($initialBalanceArray as $item)
                    if ($item['head'] == 'share') {
                        $share = $item['amount'];
                        break;
                    }
            }
        }

        $share += $heads ? $heads->total_share : 0;

        return $share;
    }
    public static function headsAmount($userId, $from = null, $to = null)
    {

        $list = [];

        $heads = DB::table("techpanda_core_account_heads as ah")
            ->join('techpanda_core_head_fees as hf', 'ah.id', '=', 'hf.head_id')
            ->join('techpanda_core_transaction_head as th', 'th.headfee_id', '=', 'hf.id')
            ->join('techpanda_core_transactions as t', 't.id', '=', 'th.transaction_id')
            ->select([DB::raw("sum(th.total) as total_amount"), 'ah.code'])
            ->groupBy('ah.code')
            ->where("t.status", "paid")
            ->where("t.user_id", $userId);

        //filter users

        if (!empty($from))
            $heads->whereDate('t.tnx_date', '>=', $from);

        if (!empty($to))
            $heads->whereDate('t.tnx_date', '<=', $to);

        // traceLog(vsprintf(str_replace('?', '%s', $heads->toSql()), collect($heads->getBindings())->map(function ($binding) {
        //     return is_numeric($binding) ? $binding : "'{$binding}'";
        // })->toArray()));

        $heads = $heads->get()->pluck('total_amount', 'code');


        $allHead = AccountHead::orderBy('sort_order', 'asc')->get();

        //initial balance
        if ($initialBalanceArray = User::find($userId)->initial_balance) {

            $initialBalance = [];
            foreach ($initialBalanceArray as $item)
                $initialBalance[$item['head']] = $item['amount'];
        }


        foreach ($allHead as $head) {
            $code = $head->code;
            $amount = isset($heads[$code]) ? $heads[$code] : 0;
            $amount += isset($initialBalance[$code]) ? (float) $initialBalance[$code] : 0;
            $list[$code] = $amount;
        }


        return $list;
    }

    //functions

    public static function getSavingHeadName()
    {
        return 'monthly-deposit-savings';
    }

    public static function getShareHeadName()
    {
        return 'share';
    }
}
