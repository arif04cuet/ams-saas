<?php namespace Techpanda\Core\Models;

use Model;

/**
 * Model
 */
class Bank extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'techpanda_core_banks';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $hasMany = [
        
        'branches'=>['Techpanda\Core\Models\BankBranch']
    ];
}
