<?php

namespace Techpanda\Core\Models;

use Model;

/**
 * Model
 */
class MemberRoll extends Model
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
    public $table = 'techpanda_core_member_rolls';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'string|required',
        'roll' => 'string|required',
    ];
}
