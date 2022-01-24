<?php

namespace Techpanda\Core\Classes;

use Backend;
use BackendAuth;
use BackendMenu;
use Event;
use System\Classes\SettingsManager;

class BackendMenuExtension
{

    public function boot()
    {
        $this->extendMenu();
    }

    public function extendMenu()
    {

        Event::listen('backend.page.beforeDisplay', function ($controller, $action, $params) {
            $controller->addCss('https://use.fontawesome.com/releases/v5.11.2/css/all.css');
        });

        Event::listen('backend.page.beforeDisplay', function ($controller, $action, $params) {

            if (!($controller instanceof \Backend\Controllers\Users)) {
                return;
            }

            BackendMenu::setContext('Techpanda.Core', 'main-menu-mis');
        });

        SettingsManager::instance()->registerCallback(function ($manager) {
            $manager->removeSettingItem('October.System', 'administrators');
        });

        Event::listen('system.reportwidgets.extendItems', function ($manager) {
        });
    }
}
