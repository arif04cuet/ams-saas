<?php

namespace Techpanda\Core;

use System\Classes\PluginBase;
use App;
use Event;
use File;
use Illuminate\Support\Facades\DB;
use Techpanda\Core\Classes\BackendMenuExtension;
use Techpanda\Core\Classes\BackendUserExtension;
use Techpanda\Core\Classes\EventsHandler;
use Techpanda\Core\Classes\FrontendEvents;
use Techpanda\Core\Classes\Helper;
use Techpanda\Core\Classes\Rule\FiscalYear;
use Techpanda\Core\Classes\SmsSender;
use Validator;

class Plugin extends PluginBase
{

    public $elevated = true;

    public function registerComponents()
    {
    }

    public function registerSettings()
    {
    }

    public function registerReportWidgets()
    {
        return [
            'Techpanda\Core\ReportWidgets\Dashboard' => [
                'label' => 'User Dashboard',
                'context' => 'dashboard',

            ]
        ];
    }

    public function registerSchedule($schedule)
    {

        $schedule->command('core:sendschedulesms')->dailyAt('10:00')->timezone('Asia/Dhaka')->withoutOverlapping();
        //$schedule->command('core:deleteorphanttnx')->daily()->timezone('Asia/Dhaka')->withoutOverlapping();
        $schedule->command('queue:work --tries=3')->cron('* * * * * *')->withoutOverlapping();

        //$schedule->command('queue:retry all')->cron('* * * * * *')->withoutOverlapping();
    }


    public function register()
    {
        $this->registerConsoleCommand('techpanda.core.sendschedulesms', 'Techpanda\Core\Console\SendScheduleSms');
        $this->registerConsoleCommand('techpanda.core:deleteorphanttnx', 'Techpanda\Core\Console\DeleteOrphanTransaction');
    }

    public function boot()
    {
        if (env('APP_DEBUG')) {
            DB::listen(function ($query) {
                File::append(
                    storage_path('/logs/query.log'),
                    $query->sql . '=>' . $query->sql . ' [' . implode(', ', $query->bindings) . ']' . PHP_EOL
                );
            });
        }

        Event::subscribe(new FrontendEvents());

        // Check if we are currently in backend module.
        if (!App::runningInBackend()) {
            return;
        }
        $this->extendClasses();
        Event::subscribe(new EventsHandler());

        Validator::extend('fiscalyear', FiscalYear::class);
    }

    public function extendClasses()
    {
        (new BackendUserExtension)->boot();
        (new BackendMenuExtension)->boot();
    }
}
