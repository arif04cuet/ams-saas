<?php

namespace Techpanda\Core\Classes\Bank;

use BackendAuth;
use Techpanda\Core\Models\BankStatement;
use Techpanda\Core\Models\Transaction;

class Dbbl
{
    public $data;
    public $bankStatement;
    public $user;

    public function __construct($transaction)
    {

        $this->data = $transaction->toArray();
        $this->bankStatement = new BankStatement();
        $this->user = $transaction->user;

        return $this;
    }

    public function status()
    {

        $channel = $this->data['offline_channel'];

        $statement = $this->bankStatement
            ->whereDate('transaction_date', $this->data['tnx_date'])
            ->where('transaction_branch_code', $this->data['offline_branch_id'])
            ->where('credit', $this->data['amount']);


        switch ($channel) {
            case 'branch_cd':
            case 'ab_cd':

                $statements = $statement->where('description', 'like', '%Cash Deposit%')->get();
                break;

            case 'ft_cd':

                $statements = $statement
                    ->where('description', 'like', '%FT%')
                    ->whereDate('value_date', $this->data['offline_value_date'])
                    ->get();

                break;

            case 'eft_ft':

                $statements = $statement->where('description', 'like', '%ATM CASA%')->get();
                break;

            case 'eft_nexuspay':

                $statements = $statement->where('description', 'like', '%NEXUS PAY%')->get();
                break;

            case 'eft_ibanking':

                $statements = $statement->where('description', 'like', '%EFT Cr%')->get();
                break;

            case 'eft_auto_debit':
                $statements = $statement->where('offline_ab_account_no', $this->data['offline_ab_account_no'])->get();

                break;

            case 'eft_dbbl_internet':
                $statements = $statement->where('transaction_ref_number', $this->data['offline_atmid'])->get();
                break;
        }

        return $this->getStatus($statements);;
    }

    public function getStatus($statements)
    {
        $status = $this->data['status'];

        if ($statements->count() == 1) {
            $status = Transaction::STATUS_PAID;
            $this->markAsMatch($statements->first());
        } else
            $status = $this->checkforMobileandName($statements);

        return $status;
    }
    public function checkforMobileandName($items)
    {

        $status = $this->data['status'];

        foreach ($items as $item) {
            //Cash Deposit/Monirul/01736618731
            $description = strtolower($item->description);
            $mobile  = $this->user->mobile;
            $status = $item->status;
            if ($mobile and strpos($description, strtolower($mobile)) !== false) {
                $status = Transaction::STATUS_PAID;
                $this->markAsMatch($item);
                break;
            } elseif (strpos($description, strtolower($this->user->first_name)) !== false) {
                $status = Transaction::STATUS_PAID;
                $this->markAsMatch($item);
                break;
            }
        }

        return $status;
    }

    public function markAsMatch($item)
    {
        $item->is_matched = 1;
        $item->save();
    }
}
