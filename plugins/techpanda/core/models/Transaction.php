<?php

namespace Techpanda\Core\Models;

use Backend\Models\User;
use BackendAuth;
use Illuminate\Support\Facades\DB;
use Lang;
use Mail;
use Model;
use October\Rain\Database\Builder;
use Techpanda\Core\Classes\Helper;
use Techpanda\Core\Classes\SmsSender;
use Techpanda\Core\Traits\Tenant;

/**
 * Model
 */
class Transaction extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use Tenant;

    /*
        Transaction Statues
    */
    const STATUS_PAID = 'paid';
    const STATUS_UNPAID = 'unpaid';
    const STATUS_REJECTED = 'rejected';
    const STATUS_REVIEW = 'submitted_for_review';
    const PER_MONTH_AMOUNT = 4000;
    const SHARE_VALUE = 100;

    // Payment gateway URLS

    const sandboxEndPoint =  'https://sandbox.sslcommerz.com';
    const liveEndPoint =  'https://securepay.sslcommerz.com';
    const OnlineCharge = 3; // 3%


    /**
     * @var string The database table used by the model.
     */
    public $table = 'techpanda_core_transactions';

    public $fillable = [];
    public $dates = [
        'created_at',
        'updated_at'
    ];
    /**
     * @var array Validation rules
     */
    public $rules = [];

    //relationships

    public $attachOne = [
        'receipt' => ['System\Models\File']
    ];

    public $belongsTo = [

        'user' => ['Backend\Models\User'],
        'approver' => ['Backend\Models\User'],

    ];

    public $hasMany = [
        'monthlySavins' => ['Techpanda\Core\Models\MonthlySaving', 'key' => 'transaction_id', 'otherKey' => 'id']
    ];

    public $belongsToMany = [

        'headfees' => [

            'Techpanda\Core\Models\HeadFee',
            'table' => 'techpanda_core_transaction_head',
            'key' => 'transaction_id',
            'otherKey' => 'headfee_id',
            'pivot' => ['quantity', 'total']

        ]
    ];

    //events

    public function beforeCreate()
    {
        if (!$this->tnx_id)
            $this->tnx_id = uniqid();
    }

    public function afterCreate()
    {
    }

    public function afterDelete()
    {
        $this->headfees()->delete();
        $this->monthlySavins()->delete();
        //$this->receipt->delete();
    }



    public function afterSave()
    {
        if ($this->status == self::STATUS_PAID) {

            if ($this->user->mobile)
                $this->sendSmsToMember();

            if ($this->user->email)
                $this->sendInvoiceToMember();

            $this->sendEmailToAdmin();
        } else if (!$this->is_online and $this->is_preview_submitted and $this->user->mobile) {
            $this->sendSmsToMemberAfterSubmit();
        }
    }

    //scopes

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('filterPreview', function (Builder $builder) {
            $builder->where('is_preview_submitted', 1);
        });
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }


    //functions

    public function getPaymentMethodOptions()
    {
        return [
            "2.5" => "Others ( Visa, MasterCard, bKash, Nexus etc) (2.5%)",
            "3.5" => "Amex, Qcash (3.5%)"

        ];
    }
    public function getLatestFiscalYear()
    {
        return HeadFee::distinct()->orderBy('year', 'desc')->first()->year;
    }
    public function getFiscalYearOptions()
    {
        $years = HeadFee::distinct()->orderBy('year', 'desc')->pluck('year', 'year')->all();
        return $years;
    }
    public function getQuantityValue($headFee)
    {
        $quantity = $headFee->pivot->quantity;
        if ($headFee->head->code == AccountHead::getSavingHeadName()) {
            $allMonths = $this->monthlySavins()->select(DB::raw("CONCAT(month,'-',year) AS month_year"))->pluck('month_year')->all();

            $quantity = implode('<br/>', $allMonths);
        }

        return $quantity;
    }


    public function getBranch()
    {
        return BankBranch::where('code', $this->offline_branch_id)->first();
    }

    public static function statusColors($status = null)
    {
        $colors = [
            self::STATUS_PAID => 'green',
            self::STATUS_REJECTED => 'red',
            self::STATUS_REVIEW => 'gray',
            self::STATUS_UNPAID => 'red'
        ];

        if (!is_null($status) and isset($colors[$status]))
            return $colors[$status];

        return $colors;
    }

    public function heads()
    {
        $heads = [];
        $headfees = $this->headfees;

        foreach ($headfees as $headfee) {
            $head = $headfee->head;
            $heads[$head->code] = [
                'name' => $head->name,
                'quantity' => $headfee->pivot->quantity,
                'amount' => $headfee->pivot->total
            ];
        }

        return $heads;
    }

    public function sendSmsToMemberAfterSubmit()
    {
        $data = [
            'name' => $this->user->full_name,
            'amount' => $this->amount,
            'month' => date("F. Y")
        ];
        $msg = Lang::get('techpanda.core::lang.sms.member_notification_after_submit', $data);
        $mobiles = [$this->user->mobile];
        $smsClient = new SmsSender($this->association);
        $smsClient->send($mobiles, $msg);
    }


    public function sendSmsToMember()
    {
        $data = [
            'name' => $this->user->full_name,
            'amount' => $this->amount,
            'month' => date("F. Y")
        ];
        $msg = Lang::get('techpanda.core::lang.sms.member_notification_after_paid', $data);
        $mobiles = [$this->user->mobile];
        $smsClient = new SmsSender($this->association);
        $smsClient->send($mobiles, $msg);
    }

    public function sendInvoiceToMember()
    {
        $member = $this->user;
        $branch = $this->getBranch();
        $vars = [
            'name' => $member->full_name,
            'transaction' => [
                'status' => $this->status,
                'channel' => $this->getOfflineChannelByCode($this->offline_channel),
                'tnx_date' => $this->tnx_date,
                'offline_value_date' => $this->offline_value_date,
                'offline_atmid' => $this->offline_atmid,
                'branch' => $branch ? $branch->name . '-' . $branch->code : null,
                'amount' => $this->amount
            ]
        ];

        $headfees = [];
        foreach ($this->headfees as $headfee) {
            $headfees[] = [
                'name' => $headfee->head->name,
                'fee' => $headfee->fee,
                'quantity' => $this->getQuantityValue($headfee),
                'total' => $headfee->pivot->total,
            ];
        }
        $vars['transaction']['headfees'] = $headfees;


        Mail::queue('techpanda.core::mail.deposit-invoice-member', $vars, function ($message) use ($member) {

            $message->to($member->email, $member->full_name);
        });
    }


    public function sendEmailToAdmin()
    {
        $tenant = $this->association;
        $vars = [
            'name' => $this->user->full_name,
            'amount' => $this->amount,
            'month' => date("F. Y")
        ];

        Mail::queue('techpanda.core::mail.after-paid-admin', $vars, function ($message) use ($tenant) {

            $message->to($tenant->email, 'Admin Person');
        });
    }


    public static function unpaidMemberLists($month = null)
    {
        if (is_null($month))
            $month = date("F");

        list($from, $to) = explode('-', (new Transaction())->getLatestFiscalYear());
        $curMonth = date("F");

        return User::where(Helper::getTenantField(), '=', Helper::getAssociationId())
            ->whereNotIn('id', function ($q) use ($from, $to, $month) {
                $q->select('user_id')
                    ->from((new MonthlySaving())->getTable())
                    ->whereBetween('year', [$from, $to])
                    ->where('month', $month);
            })->get();
    }


    public function getPaidMonthsByFiscalYear($fiscalYear, $userId, $status = [])
    {

        if (empty($status))
            $status = [Transaction::STATUS_PAID, Transaction::STATUS_REVIEW];

        $heads = DB::table('techpanda_core_transactions as t')
            ->join('techpanda_core_monthly_savings as ms', 't.id', '=', 'ms.transaction_id')
            ->select([DB::raw("group_concat(ms.month) as months"), 't.title'])
            ->groupBy('t.title')
            ->where('t.title', $fiscalYear)
            ->where('t.user_id', $userId)
            ->where("t.is_preview_submitted", 1)
            ->whereIn("t.status", $status)
            ->get();

        if (isset($heads[0]) and $data = $heads[0])
            return explode(',', $data->months);

        return [];
    }

    public static function getMonthsByFiscalYear($fiscalYear)
    {

        list($from, $to) = explode('-', $fiscalYear);

        $months = [];
        //last 6 months from year
        $m = 6;
        for ($i = 1; $i <= 12; $i++) {

            $month = $m + $i;
            $year = $from;

            if ($i > 6) {
                $month = $i - $m;
                $year = $to;
            }

            //already paid
            $months[] = date('F-Y', mktime(0, 0, 0, $month, 1, $year));
        }

        return $months;
    }
    public function getMonthlyDepositSavingsOptions()
    {

        $fiscalYear = $this->fiscal_year ?: $this->getLatestFiscalYear();

        list($from, $to) = explode('-', $fiscalYear);

        $months = [];
        $userId = BackendAuth::getUser()->id;
        $alreadyPaid = $this->getPaidMonthsByFiscalYear($fiscalYear, $userId);
        //last 6 months from year
        $m = 6;
        for ($i = 1; $i <= 12; $i++) {

            $month = $m + $i;
            $year = $from;

            if ($i > 6) {
                $month = $i - $m;
                $year = $to;
            }

            //already paid
            $paidMonth = date('F', mktime(0, 0, 0, $month, 1, $year));
            if (!in_array($paidMonth, $alreadyPaid)) {
                $mY = date('F-Y', mktime(0, 0, 0, $month, 1, $year));
                $months[$mY] = $mY;
            }
        }

        return $months;
    }

    public function getMonthsOptions()
    {
        $months = [];

        for ($i = 0; $i < 6; $i++) {
            $mY = date("F-Y", strtotime("-$i months"));
            $months[$mY] = $mY;
        }

        return $months;
    }

    public function currentFiscalYser()
    {
        return (date('m') <= 6) ? (date('Y') - 1) . '-' . date('Y') : date('Y') . '-' . (date('Y') + 1);
    }
    public function getAccountHeadsOptions()
    {
        $fiscalYear = $this->fiscal_year ?: $this->getLatestFiscalYear();
        $month = date("n");
        //if configured latest fiscal year is current then add month filtering restrictions, otherwise not
        return AccountHead::whereHas('headfees', function ($q) use ($fiscalYear, $month) {
            $q->where('year', $fiscalYear)->when($fiscalYear == $this->currentFiscalYser(), function ($q) use ($month) {
                return $q->whereIn('month', ['all', $month]);
            });
        })->pluck('name', 'code');
    }

    public function getOfflineBranchIdOptions()
    {
        $list = [];
        $branches = BankBranch::pluck('name', 'code');

        foreach ($branches->toArray() as $code => $name) {
            $list[$code] = $name . ' - ' . $code;
        }
        return $list;
    }

    public function filterFields($fields, $context = null)
    {


        $fields->offline_branch_id->value = '';


        //channel hide/shoe
        switch ($this->offline_channel) {
            case 'eft_nexuspay':
                $fields->offline_atmid->hidden = true;
                $fields->offline_branch_id->value = 100;
                $fields->offline_branch_id->readOnly = true;
                break;
            case 'eft_ibanking':
                $fields->offline_atmid->hidden = true;
                $fields->offline_branch_id->value = 747;
                $fields->offline_branch_id->readOnly = true;
                break;
            case 'ab_cd':
                $fields->offline_atmid->hidden = true;
                $fields->offline_branch_id->value = 701;
                $fields->offline_branch_id->readOnly = true;
                break;

            case 'eft_ft':
                $fields->offline_atmid->hidden = true;
                $fields->offline_atmid->hidden = false;
                $fields->offline_branch_id->readOnly = true;

                if ($this->offline_atmid) {
                    $branch = BankBranch::getBranchByAtmId($this->offline_atmid);
                    $fields->offline_branch_id->value = $branch ? $branch->code : '';
                }
                break;

            case 'eft_dbbl_internet':
                $fields->offline_atmid->hidden = false;
                $fields->offline_atmid->label = 'Host Reference Number';
                $fields->offline_branch_id->readOnly = true;

                if ($this->offline_atmid) {
                    $branch = BankBranch::getBranchByAtmId($this->offline_atmid);
                    $fields->offline_branch_id->value = $branch ? $branch->code : '';
                }
                break;

            default:
                $fields->offline_atmid->hidden = true;
                break;
        }

        //payment mode
        $data = post();
        if (isset($data['_payment_mode']))
            switch ($data['_payment_mode']) {
                case 'online':
                    $fields->offline_channel->hidden = true;
                    $fields->tnx_date->hidden = true;
                    $fields->offline_value_date->hidden = true;
                    $fields->offline_atmid->hidden = true;
                    $fields->offline_branch_id->hidden = true;
                    $fields->amount->hidden = true;
                    $fields->receipt->hidden = true;
                    // total with oneline charge

                    $total = $this->getTotalValue();
                    $charge = $fields->_payment_method->value;
                    $grandTotal = $this->getTotalWithOnlineCharge($charge);
                    //$label ='Total with online charge [ ' . $total . ' + (' . $total . ' * ' . $charge . ')/100 = ' . $grandTotal . ' Tk.]';
                    $label = 'Total Amount ';
                    $fields->_total_with_charge->label = $label;
                    $fields->_total_with_charge->comment = '<span class="text-danger">2.5-3.5 % online charge applicable with this total amount </span>';
                    $fields->_total_with_charge->commentHtml = true;

                    $fields->_total_with_charge->value = $grandTotal;

                    break;
            }

        // acount heads

        $heads = AccountHead::get();
        $total = 0;
        $fiscalYear = $this->fiscal_year ?: $this->getLatestFiscalYear();

        if (is_array($this->account_heads)) {

            foreach ($heads as $head) {

                $field = $head->code;
                if (in_array($field, $this->account_heads)) {

                    $fields->{$field}->hidden = !$fields->{$field}->hidden ? 1 : 0;
                    $fee = HeadFee::latestValue($field, $fiscalYear)->first();
                    $fields->{$field}->comment = 'Unit value: ' . $fee->fee . ' TK';
                }
            }
        }

        $fields->total->value = $this->getTotalValue();
    }

    public static function getTotalWithOnlineCharge($charge = 2.5)
    {
        $total = self::getTotalValue();
        return $total; //+ (($total * $charge) / 100);
    }
    public static function getTotalValue()
    {
        $data = post();
        $total = 0;
        $heads = AccountHead::get();
        $sum = 0;
        foreach ($heads as $head) {
            $code = $head->code;
            if (!isset($data[$code]) || empty($data[$code]))
                continue;

            $formValue = $data[$code];
            $unitValue = self::getHeadUnitValue($code, $data['fiscal_year']);
            if (is_array($formValue))
                $sum += $unitValue * count($formValue);
            else
                $sum += $unitValue * $formValue;
        }

        return $total + $sum;
    }
    public static function getHeadUnitValue($code, $year)
    {

        $fee = self::getHeadFeeByCode($code, $year);

        return $fee ? $fee->fee : 0;
    }

    public static function getHeadFeeByCode($code, $year = null)
    {

        $fee = HeadFee::latestValue($code, $year)->first();

        return $fee;
    }

    public function getOfflineChannelByCode($code)
    {
        $channels = $this->getOfflineChannelOptions();
        if (isset($channels[$code]))
            return $channels[$code];

        return false;
    }
    public function getOfflineChannelOptions()
    {
        $channels = [
            'branch_cd' => 'Cash Deposited through DBBL Bank Branch',
            'ft_cd' => 'Cash Deposited through DBBL Fast Track',
            'ab_cd' => 'Cash Deposit through DBBL Agent Banking',
            'eft_ft' => 'EFT through DBBL ATM',
            'eft_nexuspay' => 'EFT through DBBL NexusPay',
            'eft_dbbl_internet' => 'EFT through DBBL internet banking',
            'eft_auto_debit' => 'EFT through DBBL Auto Debit',
            'eft_ibanking' => 'EFT through Other Bank',
        ];

        return $channels;
    }

    public function getPaymentModeAttribute($mode)
    {
        return 'offline';
    }


    //payment gateway enpoints

    public static function getGatewayUrl()
    {
        $url = self::sandboxEndPoint;

        if (env('SSL_PAYMENT_MODE') == 'live')
            $url = self::liveEndPoint;

        return $url;
    }

    public static function getPerMonthSaving()
    {
        return self::PER_MONTH_AMOUNT;
    }
    public static function createSessionUrl()
    {
        return self::getGatewayUrl() . '/gwprocess/v4/api.php';
    }

    public static function validationUrl()
    {
        return self::getGatewayUrl() . '/validator/api/validationserverAPI.php';
    }
}
