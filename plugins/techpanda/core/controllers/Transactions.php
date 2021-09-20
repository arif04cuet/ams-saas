<?php

namespace Techpanda\Core\Controllers;

use Backend;
use Backend\Classes\Controller;
use BackendAuth;
use BackendMenu;
use Flash;
use Illuminate\Support\Facades\DB;
use Queue;
use Techpanda\Core\Classes\Export\QTransactionExport;
use Techpanda\Core\Classes\Export\TransactionExport;
use Techpanda\Core\Classes\Helper;
use Techpanda\Core\Models\AccountHead;
use Techpanda\Core\Models\HeadFee;
use Techpanda\Core\Models\MonthlySaving;
use Techpanda\Core\Models\Transaction;
use Techpanda\Core\Traits\ListPopup;
use Vdomah\Excel\Classes\Excel;


class Transactions extends Controller
{
    use ListPopup;

    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
        'Backend\Behaviors\ReorderController'
    ];


    public $formConfig = 'config_transaction_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public $listConfig = [

        'transactions' => 'config_transaction_list.yaml',
        'head_fees' => 'config_head_fees_list.yaml',
        'account_heads' => 'config_account_heads_list.yaml',
        'bank_statements' => 'config_bank_statements_list.yaml',
        'bank_branches' => 'config_bank_branches_list.yaml',
    ];


    public $requiredPermissions = [
        'techpanda.core.manage_transactions'
    ];

    public function __construct()
    {

        $this->vars['mode'] = false;

        if (post('mode')) {
            $mode = post('mode');
            $this->vars['mode'] = $mode;
            $this->formConfig = 'config_' . $mode . '_form.yaml';
        }

        parent::__construct();
        BackendMenu::setContext('Techpanda.Core', 'main-menu-mis', 'side-menu-transaction');
    }
    public function downloadExcel()
    {
        //excel export
        $export = new TransactionExport(Helper::getAssociationId());
        $filename = 'transactions_' . date("Y_m_d_H_i");
        return Excel::export($export, $filename, 'xlsx');
    }

    public function downloadQReport()
    {
        //excel export

        $fiscalYear = request('fiscalYear');
        $quarter = request('quarter');

        $export = new QTransactionExport(Helper::getAssociationId(), $fiscalYear, $quarter);
        $filename = 'quarterly_report_' . $quarter . '_' . $fiscalYear;
        return Excel::export($export, $filename, 'xlsx');
    }
    public function onSubmitQReportRequest()
    {
        $fiscalYear = post('fiscal-year');
        $quarter = post('quarter');

        MonthlySaving::getTotalSavings();
        return 'done';
        //return Backend::redirect('techpanda/core/transactions/downloadQReport?fiscalYear=' . $fiscalYear . '&quarter=' . $quarter);
    }

    public function index()
    {
        //excel export
        // $export = new TransactionExport(Helper::getAssociationId());
        // return Excel::export($export, 'transactions', 'xlsx');

        $this->asExtension('ListController')->index();
        $this->bodyClass = 'compact-container';
    }

    public function onViewTransaction()
    {
        $transaction = Transaction::findOrFail(post('record_id'));
        return $this->makePartial('view_transaction', ['transaction' => $transaction]);
    }

    public function onStatusUpdate()
    {
        $transaction = Transaction::findOrFail(post('id'));
        $transaction->status = post('status') == 'paid' ? 'paid' : 'rejected';
        $transaction->approver = BackendAuth::getUser();
        $transaction->approval_date = date("y-m-d H:i:s");
        $transaction->save();

        if (post('status') == Transaction::STATUS_PAID)
            $msg = 'Transation has been approved and notification sent to member';
        else
            $msg = 'Transation has been rejected';

        Flash::success($msg);

        //return $this->listRefresh('transactions');

        return Backend::redirect('techpanda/core/transactions');
    }



    public function getCurrentMonthRecord()
    {
        $currentMonth = date('m');
        return $this->widget->transactions->model::whereRaw('MONTH(created_at) = ?', [$currentMonth])->get()->count();
    }

    public function listExtendQuery($query)
    {
        if (isset($this->widget->transactions)) {
            $this->widget->transactions->recordsPerPage = 100;
        }
    }

    public function onShowUnpaidUsers()
    {
        $this->vars['users'] = Transaction::unpaidMemberLists();
        return $this->makePartial('unpaid_user_list');
    }

    public function scoreBoards()
    {
        $currentMonth = date('m');

        $transactions = Transaction::whereMonth('created_at', $currentMonth)
            ->select([DB::raw("count(amount) as total_count"), DB::raw("sum(amount) as total_amount"), 'status'])
            ->whereIn('status', [Transaction::STATUS_PAID, Transaction::STATUS_REVIEW])
            ->groupBy('status')
            ->orderBy('status')
            ->get()
            ->toArray();

        return $transactions;
    }

    public function unPaid()
    {
        $members = Transaction::unpaidMemberLists()->count();
        $monthlyDeposit = HeadFee::latestValue('monthly-deposit-savings')->first();
        return [
            'members' => $members,
            'unpaid_amount' => $monthlyDeposit ? number_format($members * $monthlyDeposit->fee) : 0
        ];
    }

    public function onSendSms()
    {

        $users = Transaction::unpaidMemberLists()->pluck('mobile', 'id')->toArray();
        $mobiles = array_values($users);
        $tenantId = Helper::getAssociationId();
        $msg = post('msg');

        foreach ($mobiles as $number) {

            $data = [
                'tenantId' => $tenantId,
                'number' => $number,
                'msg' => $msg
            ];

            Queue::push('Techpanda\Core\Classes\Jobs\SendSms', $data);
        }

        $msg = 'Sms send successfully';
        Flash::success($msg);

        return $mobiles;
    }
}
