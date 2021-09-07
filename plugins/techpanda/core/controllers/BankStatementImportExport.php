<?php

namespace Techpanda\Core\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class BankStatementImportExport extends Controller
{
    public $implement = [
        'Backend.Behaviors.ImportExportController'
    ];

    public $importExportConfig = 'config_import_export.yaml';

    public $requiredPermissions = [
        'techpanda.core.staement_import_export'
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Techpanda.Core', 'main-menu-mis', 'side-menu-transaction');
    }
}
