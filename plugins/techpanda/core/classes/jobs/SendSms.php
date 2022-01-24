<?php

namespace Techpanda\Core\Classes\Jobs;

use Backend\Models\User;
use Mail;
use Techpanda\Core\Classes\SmsSender;
use Techpanda\Core\Models\Association;

class SendSms
{
    public function fire($job, $data)
    {

        //send schedule sms to members
        $tenant = Association::find($data['tenantId']);
        $smsSender = new SmsSender($tenant);
        $number = str_replace(' ', '', trim($data['number']));
        $smsSender->send([$number], $data['msg']);
        $job->delete();
    }
}
