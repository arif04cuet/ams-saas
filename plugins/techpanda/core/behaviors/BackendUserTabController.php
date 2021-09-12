<?php

namespace Techpanda\Core\Behaviors;

use Backend\Classes\ControllerBehavior;
use Backend\Models\User;
use Event;
use Flash;

class BackendUserTabController extends ControllerBehavior
{
    public function __construct($controller)
    {
        parent::__construct($controller);
    }

    public function onApproved($recordId)
    {
        $member = User::where('id', $recordId)->where('is_activated', 0)->first();

        if (!$member)
            return;

        $member->is_activated = true;
        $member->activated_at = now();
        $member->save();

        Event::fire('member.approved', [$member]);

        Flash::success('User has been Approved Successfully');
        if ($redirect = $this->controller->makeRedirect()) {
            return $redirect;
        }
    }

    public function onClose()
    {

        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {

            foreach ($checkedIds as $userId) {
                User::find($userId)->update([
                    'is_activated' => false,
                    'deleted_at' => now()
                ]);
            }
        }

        return $this->controller->listRefresh();
    }
}
