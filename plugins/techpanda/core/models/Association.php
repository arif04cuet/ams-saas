<?php

namespace Techpanda\Core\Models;

use BackendAuth;
use Model;
use Techpanda\Core\Classes\Helper;

/**
 * Model
 */
class Association extends Model
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at', 'establishment'];

    public $jsonable = ['sms_schedule'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'techpanda_core_associations';


    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required',
        'establishment' => 'required|date',
        'email' => 'required'
    ];

    public $attachOne = [
        'logo' => 'System\Models\File'
    ];
    public $attachMany = [
        'documents' => 'System\Models\File'
    ];

    public $hasMany = [
        'members' => ['Backend\Models\User', 'order' => 'login asc'],
        'committees' => 'Techpanda\Core\Models\Committee',
        'contents' => 'Techpanda\Core\Models\Content'
    ];

    public $belongsTo = [

        'bank' => ['Techpanda\Core\Models\Bank']
    ];


    public function AfterFetch()
    {
    }

    public function getSmsConfig()
    {
        return [
            'sms_gateway' => $this->sms_gateway,
            'sms_username' => $this->sms_username,
            'sms_password' => $this->sms_password,
            'sms_api_key' => $this->sms_api_key

        ];
    }
}
