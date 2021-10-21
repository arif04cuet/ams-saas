<?php

namespace Techpanda\Core\ReportWidgets;

use ApplicationException;
use Backend;
use Backend\Classes\ReportWidgetBase;
use Backend\Models\BrandSetting;
use Backend\Models\AccessLog;
use Backend\Models\User;
use Exception;
use BackendAuth;
use Renatio\DynamicPDF\Classes\PDF;
use File;
use Flash;
use Http;
use Input;
use Lang;
use NumberFormatter;
use Redirect;
use System\Models\File as ModelsFile;
use Techpanda\Core\Classes\Helper;
use Techpanda\Core\Classes\SslCommerceClient;
use Techpanda\Core\Models\AccountHead;
use Techpanda\Core\Models\Association;
use Techpanda\Core\Models\Content;
use Techpanda\Core\Models\HeadFee;
use Techpanda\Core\Models\MonthlySaving;
use Techpanda\Core\Models\Transaction;
use ValidationException;
use Validator;

use function Matrix\trace;

class Dashboard extends ReportWidgetBase
{
    /**
     * @var string A unique alias to identify this widget.
     */
    protected $defaultAlias = 'memberdashboard';
    protected $tnxForm = null;
    protected $statementForm = null;
    protected $tnxList = null;

    protected $user = null;


    public function init()
    {

        if (!isset($this->tnxForm)) {
            $this->tnxForm = $this->makeTnxForm();
        }
        if (!isset($this->tnxList)) {
            $this->tnxList = $this->makeTnxList();
        }

        if (!isset($this->user)) {
            $this->user = BackendAuth::getUser();
        }

        $this->vars['tnx_list'] = $this->tnxList;
    }


    public function onLoadSingleContent()
    {
        $id = input('id');
        $this->vars['content'] = Content::find($id);
        return $this->makePartial('single_content');
    }

    public function onViewMember()
    {

        $this->vars['user'] =  input('login') ? BackendAuth::findUserByLogin(input('login')) : $this->user;;
        $this->vars['loggedIn'] =  input('login') ? false : true;
        return [
            '#memberDetails' => $this->makePartial('member_details')
        ];
    }
    /**
     * Renders the widget.
     */
    public function render()
    {
        try {
            $this->loadData();
        } catch (Exception $ex) {
            $this->vars['error'] = $ex->getMessage();
        }

        return $this->makePartial('widget');
    }


    protected function loadData()
    {

        $this->vars['user'] = $user = $this->user;
        $this->vars['heads'] = AccountHead::headsAmount($user->id);
        $this->vars['totalShare'] = AccountHead::getShareCount($user->id);

        $this->vars['fiscalYears'] = (new Transaction())->getFiscalYearOptions();

        $this->vars['app'] = BrandSetting::getSettingsRecord();
        $this->vars['appName'] = BrandSetting::get('app_name');
        $this->vars['lastSeen'] = AccessLog::getRecent($user);

        //
        $association_id = Helper::getAssociation()->id;

        $this->vars['association'] = Association::find($association_id);
        $userQuery =  User::with(['avatar', 'role'])
            ->where('association_id', Helper::getAssociationId())
            ->where(function ($q) {
                $q->where('is_activated', 1)->orWhereNotNull('deleted_at');
            })
            ->withTrashed()
            ->orderBy('login', 'asc');

        traceLog(vsprintf(str_replace('?', '%s', $userQuery->toSql()), collect($userQuery->getBindings())->map(function ($binding) {
            return is_numeric($binding) ? $binding : "'{$binding}'";
        })->toArray()));

        $this->vars['members'] = $userQuery->get();
        $this->vars['contents'] = Content::with('category')->latest()->get();

        // transaction details form
        $this->vars['tnx_form'] = $this->tnxForm;
    }


    public function makeTnxForm()
    {
        $fields = '$/techpanda/core/models/transaction/fields.yaml';
        $config = $this->makeConfig($fields);
        $config->model = new Transaction;
        $config->alias = 'onChangeDropdownTnxForm';
        $widget = $this->makeWidget('Backend\Widgets\Form', $config);
        $widget->bindToController();

        return $widget;
    }

    public function makeTnxList()
    {
        $fields = '$/techpanda/core/models/transaction/columns_members.yaml';
        $config = $this->makeConfig($fields);
        $config->model = new Transaction;
        $config->alias = 'onTnxList';
        $listWidget = $this->makeWidget('Backend\Widgets\Lists', $config);
        $listWidget->bindToController();

        // filter logged in user
        $listWidget->bindEvent('list.extendQueryBefore', function ($query) {
            $query->where('user_id', $this->user->id);
        });

        return $listWidget;
    }

    public function loadUsers($association_id)
    {
        $members = Association::find($association_id)->members();

        return $members;
    }

    //events

    public function onSubmitDepositStatementRequest()
    {

        $user = $this->user;
        $fiscalYear = post('fiscal-year');
        list($from, $to) = explode('-', $fiscalYear);

        $fiscalMonths = Transaction::getMonthsByFiscalYear($fiscalYear);

        $months = MonthlySaving::whereHas('transaction', function ($q) {
            return $q->where('status', Transaction::STATUS_PAID);
        })->where('user_id', $user->id)
            ->where(function ($q) use ($fiscalMonths) {
                foreach ($fiscalMonths as $monthYear) {
                    list($month, $year) = explode('-', $monthYear);
                    $q->orWhere(function ($q) use ($year, $month) {
                        $q->where('month', $month)->where('year', $year);
                    });
                }
            });


        $months = $months->get()->toArray();

        //sort date according to fiscal year

        usort($months, function ($a, $b) {
            $dateA = "01 " . $a['month'] . " " . $a['year'];
            $dateB = "01 " . $b['month'] . " " . $b['year'];
            return strtotime($dateA) - strtotime($dateB);
        });

        $tnxFrom = $from . '-07-01';
        $tnxTo = $to . '-06-01';

        $totalShare = AccountHead::getShareCount($user->id, $tnxFrom, $tnxTo, false);

        // get unit value
        $headFee = HeadFee::whereHas('head', function ($q) {
            $q->where('code', AccountHead::getSavingHeadName());
        })->where('year', $fiscalYear)->first();

        $monthlyFee = $headFee->fee;
        $total = count($months) * $monthlyFee;

        $data['fiscalYear'] = $fiscalYear;
        $data['months'] = $months;
        $data['monthlyFee'] = $monthlyFee;
        $data['totalShare'] = $totalShare;
        $data['total'] = $total;
        $data['user'] = $this->user->toArray();

        $data['inWords'] = (new NumberFormatter("en", NumberFormatter::SPELLOUT))->format($total);


        $fileName = rand() . '.pdf';
        $path = storage_path('temp/public/') . $fileName;
        $templateCode = 'deposit-certificate';
        $save = PDF::loadTemplate($templateCode, $data)->save($path);

        return redirect('/storage/temp/public/' . $fileName);
    }
    public function onSubmitStatementRequest()
    {

        $user = $this->user;
        $from = $to = null;

        $data = post();

        $transactions = Transaction::where('user_id', $user->id)->where('status', Transaction::STATUS_PAID);

        if ($from = $data['from']) {
            $transactions->whereDate('tnx_date', '>=', $from);
        }
        if ($to = $data['to']) {
            $transactions->whereDate('tnx_date', '<=', $to);
        }

        $transactions = $transactions->get();
        $data['transactions'] = $transactions;
        $data['heads'] = AccountHead::headsAmount($user->id, $from, $to);
        $data['note'] = Lang::get('techpanda.core::lang.emailtemplate.deposite_statement_note');
        $data['user'] = $this->user->toArray();

        $fileName = rand() . '.pdf';
        $path = storage_path('temp/public/') . $fileName;
        $templateCode = 'deposit-statement';


        $data['to'] = $data['to'] ? $data['to'] : date("Y-m-d");

        $save = PDF::loadTemplate($templateCode, $data)->save($path);

        return redirect('/storage/temp/public/' . $fileName);
    }

    public function onCancelPreview()
    {

        Transaction::withoutGlobalScopes()->findOrFail(post('id'))->delete();
    }

    public function onPreviewSubmit()
    {

        $transaction = Transaction::withoutGlobalScopes()->findOrFail(post('id'));
        $transaction->is_preview_submitted = 1;
        $transaction->save();

        //redirect to dashboard

        $msg = Lang::get('techpanda.core::lang.message.after_submit_deposit_success');
        Flash::success($msg);

        return Backend::redirect('/');
    }
    public function onSubmitTnx()
    {

        $data = post();

        $rules = [
            'fiscal_year' => 'required|fiscalyear',
            'account_heads' => 'array'
        ];

        $messages = [
            'account_heads.array' => ' Select at least one account head',
            'monthly-deposit-savings.array' => ' Select at least one Saving Month',
            'months.array' => 'Pls select month (s)',
            'tnx_date.required' => 'Pls select transaction date',
            'offline_value_date.required_if' => ' pls select value date',
            'offline_atmid.required_if' => ' pls enter ATM Id',
            'offline_ab_account_no.required_if' => ' pls enter auto debit account No',
            'offline_branch_id.required' => 'Pls select a branch',
            'receipt.required_if' => 'Pls upload recept',
            'user_id.required' => 'You have already submitted your deposit for this month'
        ];

        $heads = AccountHead::get();

        foreach ($heads as $head) {
            $code = $head->code;
            $validation = 'sometimes|required';
            if ($code == 'monthly-deposit-savings')
                $validation = $validation . '|array';

            $rules[$code] = $validation;
        };


        $rules['tnx_date'] = 'sometimes|required';
        $rules['offline_value_date'] = 'required_if:offline_channel,ft_cd';
        $rules['offline_atmid'] = 'required_if:offline_channel,eft_ft';
        $rules['offline_ab_account_no'] = 'required_if:offline_channel,eft_auto_debit';
        $rules['offline_branch_id'] = 'sometimes|required';
        $rules['amount'] = 'sometimes|required|same:total';


        $transaction = new Transaction();

        if ($data['_payment_mode'] == 'offline') {

            //upload receipt if any
            $receipt = $transaction
                ->receipt()
                ->withDeferred($data['_session_key'])
                ->latest()
                ->first();
            if (!$receipt)
                $rules['receipt'] = 'required_if:_payment_mode,offline';
        }

        // restrict duplicate submition
        if (Transaction::whereMonth('created_at', date('m'))->where('user_id', $this->user->id)->first()) {
            //$rules['user_id'] = 'required';
        }

        $validation = Validator::make($data, $rules, $messages);

        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        //save transaction

        if ($data['_payment_mode'] == 'offline') {
            $transaction = $this->saveOfflineTransaction($transaction, $data, $heads);
            //show confirmation preview modal
            return $this->makePartial('preview', ['transaction' => $transaction]);
        } else {
            return $this->saveOnlineTransaction($transaction, $data, $heads);
        }


        //redirect to dashboard
        return Backend::redirect('/');
    }
    public function saveRelationData($transaction, $data, $heads)
    {
        //save relation transaction & heads
        foreach ($heads as $head) {
            $code = $head->code;
            if (!isset($data[$code]) || empty($data[$code]))
                continue;

            $formValue = $data[$code];
            $quantity = is_array($formValue) ? count($formValue) : $formValue;

            //save relation
            $headfee = Transaction::getHeadFeeByCode($code, $data['fiscal_year']);
            $amount = $headfee->fee * $quantity;
            $pivotData = [
                'quantity' => $quantity,
                'total' => $amount
            ];

            $transaction->headfees()->add($headfee, $pivotData);

            //save monthly savings
            if ($code == AccountHead::getSavingHeadName() and is_array($formValue)) {

                foreach ($formValue as $monthYear) {

                    $parts = explode('-', $monthYear);
                    $savings = new MonthlySaving();
                    $savings->user = $this->user;;
                    $savings->month = $parts[0];
                    $savings->year = $parts[1];

                    $transaction->monthlySavins()->add($savings);
                }
            }
        }
    }

    public function saveOnlineTransaction($model, $data, $heads)
    {
        //create session for payment gateway
        $amountWithCharge = Transaction::getTotalWithOnlineCharge();

        $user = $this->user;
        $requestData = [
            'store_id' => env('STORE_ID'),
            'store_passwd' => env('STORE_PASSWORD'),
            'total_amount' => $amountWithCharge,
            'currency' => 'BDT',
            'tran_id' => uniqid(),
            'success_url' => config('app.url') . '/' . Backend::uri() . '/ssl/success',
            'fail_url' =>    config('app.url') . '/' . Backend::uri() . '/ssl/fail',
            'cancel_url' => config('app.url') . '/' . Backend::uri() . '/ssl/cancel',
            'ipn_url' => config('app.url') . '/' . Backend::uri() . '/ssl/ipn',
            'cus_name' => $user->first_name . ' ' . $user->last_name,
            'cus_email' =>   $user->email,
            'cus_add1' =>    $user->work_address ?: 'no address',
            'cus_add2' =>    $user->work_address ?: 'no address',
            'cus_city' =>    'dhaka',
            'cus_country' => 'Bangladesh',
            'cus_phone' =>   $user->mobile ?: '0000000000',
            'shipping_method' => 'NO',
            'num_of_item' => 2,
            'product_name' =>    'deposit - ' . date("m") . "-" . $data['total'],
            'product_category' =>   'monthly deposit',
            'product_profile' => 'general'

        ];


        //send request to sslcommerce

        $result = Http::post(Transaction::createSessionUrl(), function ($http) use ($requestData) {

            $http->data($requestData);
            if (!config('app.debug'))
                $http->verifySSL();
        });


        $sslResponse = json_decode($result->body, true);


        if ($result->code == 200 && isset($sslResponse['GatewayPageURL']) && $sslResponse['GatewayPageURL'] != "") {

            //save payment gateway session key

            $model->title = $data['fiscal_year'];
            $model->sessionkey = $sslResponse['sessionkey'];
            $model->tnx_id = $requestData['tran_id'];

            //save initial transaction data
            $model->tnx_date = date("Y-m-d: h:i:s");
            $model->is_online = 1;
            $model->amount = $data['total'];
            $model->status = Transaction::STATUS_REJECTED;
            $model->is_preview_submitted = 1;
            $model->user = $this->user;;

            $model->save();

            $this->saveRelationData($model, $data, $heads);

            //redirecto gateway url
            return Redirect::to($sslResponse['GatewayPageURL']);
        } else {

            $msg = 'FAILED TO CONNECT WITH SSLCOMMERZ API';
            Flash::error($msg);
        }

        return $model;
    }
    public function saveOfflineTransaction($transaction, $data, $heads)
    {
        $transaction->title = $data['fiscal_year'];
        $transaction->offline_channel = $data['offline_channel'];
        $transaction->tnx_date = $data['tnx_date'];
        $transaction->offline_branch_id = $data['offline_branch_id'];
        $transaction->amount = Transaction::getTotalValue(); // $data['amount'];
        $transaction->note = $data['note'];

        $transaction->user = $this->user;
        $transaction->status = Transaction::STATUS_REVIEW;

        if ($data['offline_value_date'])
            $transaction->offline_value_date = $data['offline_value_date'];


        //upload receipt if any
        $receipt = $transaction
            ->receipt()
            ->withDeferred($data['_session_key'])
            ->latest()
            ->first();

        if ($receipt) {
            $transaction->receipt = $receipt;
        }

        $transaction->save();

        $this->saveRelationData($transaction, $data, $heads);

        return $transaction;
    }
}
