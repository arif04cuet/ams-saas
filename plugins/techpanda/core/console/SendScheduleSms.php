<?php

namespace Techpanda\Core\Console;

use Backend\Models\User;
use Illuminate\Console\Command;
use Queue;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Techpanda\Core\Classes\SmsSender;
use Techpanda\Core\Models\Association;

class SendScheduleSms extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'core:sendschedulesms';

    /**
     * @var string The console command description.
     */
    protected $description = 'Send Schedule Sms to all member os tenant';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {

        //$this->output->writeln('Hello world!');

        $tenants = Association::get();
        foreach ($tenants as $tenant) {

            if (!$tenant->is_enable_sms)
                continue;

            $scheduleSms = $tenant->sms_schedule;

            if (is_array($scheduleSms) and !empty($scheduleSms)) {


                $daysOfMonth = [];
                $todaysDay = date("j");


                foreach ($scheduleSms as $item) {
                    $daysOfMonth[$item['day_of_month']] = $item['message'];
                }

                if (in_array($todaysDay, array_keys($daysOfMonth))) {

                    $msg = $daysOfMonth[$todaysDay];
                    $mobiles = User::where('is_activated', 1)->where('association_id', $tenant->id)->pluck('mobile');
                    //$mobiles = ["01777261718", "01717348147", "01982461706"];

                    foreach ($mobiles as $number) {

                        $data = [
                            'tenantId' => $tenant->id,
                            'number' => $number,
                            'msg' => $msg
                        ];
                        Queue::push('Techpanda\Core\Classes\Jobs\SendSms', $data);
                    }
                }
            }
        }

        $this->output->writeln('done!');
    }

    /**
     * Get the console command arguments.
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }
}
