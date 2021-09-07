<?php

namespace Techpanda\Core\Controllers;

use Backend;
use Backend\Classes\Controller;
use BackendMenu;
use Redirect;
use Session;

class Association extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController'
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'techpanda.core.manage_association'
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Techpanda.Core', 'main-menu-association');
    }

    public function onSelectTenant()
    {
        $id = request('id');
        Session::put('user.association_id', (int) $id);
        return Backend::redirect('backend/users');
    }
}
