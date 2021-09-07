<?php

namespace Techpanda\Core\Models;

use Model;
use Techpanda\Core\Traits\Tenant;

/**
 * BankStatement Model
 */
class BankStatement extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use Tenant;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'techpanda_core_bank_statements';

    public $fillable = [
        'transaction_date',
        'value_date',
        'transaction_ref_number',
        'user_ref_number',
        'description',
        'transaction_branch_code',
        'debit',
        'credit',
        'balance'
    ];

    public $rules = [
        'transaction_date' => 'required',
        'transaction_ref_number' => 'required'
    ];


    protected $dates = [
        'created_at',
        'updated_at',
        'transaction_date',
        'value_date'
    ];
}
