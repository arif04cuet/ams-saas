<?php

namespace Techpanda\Core\Behaviors;

use Backend\Classes\ControllerBehavior;
use Backend\Models\User;
use Event;
use Flash;
use Techpanda\Core\Traits\ListPopup;

class BackendUserRollController extends ControllerBehavior
{


    public function __construct($controller)
    {
        parent::__construct($controller);
        $controller->vars['mode'] = $mode = post('mode');
    }

    public function onCreateForm()
    {
        $this->controller->asExtension('FormController')->create();
        return $this->controller->makePartial('rolls/create_form');
    }

    public function onCreate()
    {
        $data = request('MemberRoll');
        unset($data['permissions']);

        request()->request->add(['MemberRoll' => $data]);
        traceLog(request()->all());
        $this->controller->asExtension('FormController')->create_onSave();
        return $this->controller->listRefresh($this->vars['mode']);
    }
}
