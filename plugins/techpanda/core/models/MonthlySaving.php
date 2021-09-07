<?php namespace Techpanda\Core\Models;

use Model;

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
}
