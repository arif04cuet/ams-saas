<?php

namespace Techpanda\Core\Models;

use Backend\Models\User;
use Model;
use Techpanda\Core\Classes\Helper;

/**
 * Model
 */
class Committee extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \Techpanda\Core\Traits\Tenant;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'techpanda_core_committee';

    protected $jsonable = ['members'];

    protected $dates = [
        'valid_from',
        'valid_to'
    ];


    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required',
        'code' => 'required',
        'valid_from' => 'required|date',
        'valid_to' => 'required|date',
        'members' => 'required',
        'members.*.member' => 'required',
        'members.*.role' => 'required',
    ];


    public function getMemberOptions()
    {
        $members = Helper::getAssociation()->members->pluck('first_name', 'id');
        return $members;
    }
}
