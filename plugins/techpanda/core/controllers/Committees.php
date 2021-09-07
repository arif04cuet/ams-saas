<?php

namespace Techpanda\Core\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Committees extends Controller
{
    public $implement = ['Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'techpanda.core.manage_committee'
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Techpanda.Core', 'main-menu-mis', 'side-menu-committee');
    }
}
