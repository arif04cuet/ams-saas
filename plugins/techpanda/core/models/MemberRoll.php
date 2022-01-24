<?php

namespace Techpanda\Core\Models;

use Backend\Models\User;
use Model;
use October\Rain\Database\Traits\Purgeable;
use October\Rain\Database\Traits\SoftDelete;

/**
 * Model
 */
class MemberRoll extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use Purgeable;
    use SoftDelete;
    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'techpanda_core_member_rolls';

    public $purgeable = ['permissions'];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'string|required',
        'roll' => 'string|required',
    ];

    public static function unRegisteredMembers()
    {
        $allMembers = User::select(['login'])
            ->where('is_activated', 1)
            ->orWhereNotNull('deleted_at')
            ->get()
            ->pluck('login')
            ->map(function ($item) {
                return substr($item, -4);
            })
            ->toArray();

        $list = MemberRoll::select(['name', 'roll', 'id'])
            ->orderBy('roll')
            ->get()
            ->filter(function ($item) use ($allMembers) {
                return !in_array($item->roll, $allMembers);
            });

        return $list;
    }
}
