<?php

namespace Techpanda\Core\Models;

use Backend\Models\User;
use Model;
use Queue;
use Techpanda\Core\Classes\Helper;
use Techpanda\Core\Traits\Tenant;

/**
 * Model
 */
class Content extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use Tenant;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'techpanda_core_contents';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'title' => 'required',
        'category' => 'required'
    ];

    public $attachMany = [
        'files' => ['System\Models\File']
    ];

    public $belongsTo = [
        'category' => ['Techpanda\Core\Models\Category']
    ];

    public function afterCreate()
    {
        if ($this->send_members) {
            //send contents to memebers via emails

            $members = User::where('is_activated', 1)->where('association_id', Helper::getAssociationId())->get();

            $contentId = $this->id;
            foreach ($members as $member) {

                $data = [
                    'contentId' => $contentId,
                    'userId' => $member->id
                ];

                Queue::push('Techpanda\Core\Classes\Jobs\SendMail', $data);
            }
        }
    }
}
