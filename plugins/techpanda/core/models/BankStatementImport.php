<?php

namespace Techpanda\Core\Models;

use ApplicationException;
use Backend\Models\ImportModel;
use Backend\Models\User;
use BackendAuth;
use Exception;
use System\Classes\MediaLibrary;
use System\Models\File;
use Techpanda\Core\Classes\Helper;

class BankStatementImport extends ImportModel
{
    public $table = 'techpanda_core_bank_statements';

    public $paid = [];

    /**
     * Validation rules
     */
    public $rules = [
        'transaction_date' => 'required',
        'transaction_ref_number' => 'required'
    ];


    public function importData($results, $sessionKey = null)
    {
        $firstRow = reset($results);

        foreach ($results as $row => $data) {
            try {

                $data = array_map('trim', $data);

                $exist = $this->findDuplicate($data);

                $model = $exist ?: new BankStatement();

                $model->association_id = Helper::getAssociationId();
                $model->transaction_date = date('Y-m-d', strtotime($data['transaction_date']));
                $model->value_date = date('Y-m-d', strtotime($data['value_date']));
                $model->transaction_ref_number = $data['transaction_ref_number'];
                $model->user_ref_number = $data['user_ref_number'];
                $model->description = $data['description'];
                $model->transaction_branch_code = $data['transaction_branch_code'];
                $model->debit = floatval(str_replace(',', '', $data['debit']));
                $model->credit = floatval(str_replace(',', '', $data['credit']));
                $model->balance = floatval(str_replace(',', '', $data['balance']));

                $model->save();

                $this->logCreated();
            } catch (Exception $ex) {
                $this->logError($row, $ex->getMessage());
            }
        }

        //match transations
        $this->matchTransactions();
        //traceLog(count($this->paid));
    }

    protected function findDuplicate($data)
    {
        $transactionDate = date('Y-m-d', strtotime($data['transaction_date']));
        $trax_number = $data['transaction_ref_number'];
        $description = $data['description'];

        return BankStatement::whereDate('transaction_date', $transactionDate)
            ->where('transaction_ref_number', $trax_number)
            ->where('description', $description)
            ->first();
    }

    public function matchTransactions()
    {
        $transactions = Transaction::where('status', Transaction::STATUS_REVIEW)
            // ->whereMonth('created_at', date('m'))
            // ->whereYear('created_at', date('Y'))
            ->get();


        foreach ($transactions as $transaction) {

            if ($this->getBankChecker($transaction)->status() == Transaction::STATUS_PAID) {

                $transaction->status = Transaction::STATUS_PAID;
                $transaction->save();

                $this->paid[] = $transaction->id;
            }
        }
    }


    public function getBankChecker($transaction)
    {
        if ($bank = Helper::getAssociation()->bank) {
            $class = 'Techpanda\Core\Classes\Bank\\' . ucfirst($bank->code);
            return new $class($transaction);
        }

        return null;
    }
}
