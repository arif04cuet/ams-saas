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
        $this->controller->asExtension('FormController')->create_onSave();
        return $this->controller->listRefresh($this->controller->vars['mode']);
    }

    public function onUpdateForm()
    {
        $this->controller->asExtension('FormController')->update(post('record_id'));
        $this->controller->vars['recordId'] = post('record_id');
        return $this->controller->makePartial('rolls/update_form');
    }

    public function onUpdate()
    {
        $this->controller->asExtension('FormController')->update_onSave(post('record_id'));
        return $this->controller->listRefresh($this->controller->vars['mode']);
    }

    public function onDeleteRoll()
    {
        $this->controller->asExtension('FormController')->update_onDelete(post('record_id'));
        return $this->controller->listRefresh($this->controller->vars['mode']);
    }
}
