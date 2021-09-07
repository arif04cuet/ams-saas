<?php

namespace Techpanda\Core\Models;

use Backend\Models\ImportModel;
use Exception;


class BankBranchImport extends ImportModel
{
    public $table = 'techpanda_core_bank_branches';

    /**
     * Validation rules
     */
    public $rules = [
        'code' => 'required',
        'name' => 'required',
        'bank_id' => 'required'
    ];


    public function importData($results, $sessionKey = null)
    {
        $firstRow = reset($results);

        foreach ($results as $row => $data) {
            try {

                $data = array_map('trim', $data);

                if (empty($data['code']))
                    continue;

                $exist = $this->findDuplicate($data);

                $model = $exist ?: new BankBranch();

                $model->name = $data['name'];
                $model->code = $data['code'];
                $model->bank_id = $data['bank_id'];
                $model->address = $data['address'];


                $model->save();

                $this->logCreated();
            } catch (Exception $ex) {
                $this->logError($row, $ex->getMessage());
            }
        }
    }

    protected function findDuplicate($data)
    {


        return BankBranch::where('code', $data['code'])
            ->where('bank_id', $data['bank_id'])
            ->first();
    }

    public function getBankChecker($data)
    {
        if ($bank = Helper::getAssociation()->bank) {
            $class = 'Techpanda\Core\Classes\Bank\\' . ucfirst($bank->code);
            return new $class($data);
        }

        return null;
    }
}
