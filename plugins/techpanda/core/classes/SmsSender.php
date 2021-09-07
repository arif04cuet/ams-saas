<?php

namespace Techpanda\Core\Classes;

use Backend\Models\User;
use Techpanda\Core\Models\Association;

class SmsSender
{

    public $tenant = null;

    public function __construct(Association $association)
    {

        $this->tenant = $association;
    }


    public function send($recepients, $message)
    {

        if (!is_array($recepients))
            $recepients = [$recepients];


        $response = '';

        $contacts = [];
        foreach ($recepients as $recepient) {

            if ($this->validMobile($recepient))
                $contacts[] = $recepient;
        }

        if ($contacts) {
            $contacts = implode('+', $contacts);
            $response = $this->send_sms($contacts, $message);
        }

        return $response;
    }

    public function validMobile($mobile)
    {
        $mobile = substr(trim($mobile), -11);

        return strlen($mobile) == 11 ? '88' . $mobile : false;
    }
    private function getSmsGateway()
    {
        $url = '';
        $gateway = $this->tenant->sms_gateway;

        switch ($gateway) {
            case 'smsbuzzbd':
                $url = 'http://bulksms.smsbuzzbd.com/smsapi';
                break;
        }

        return $url;
    }

    private function send_sms($contacts, $message)
    {
        $log = 'sms sent - ' . $message . ' to ' . $contacts;
        traceLog($log);

        if (env('APP_DEBUG'))
            return '';

        // check sms gateway is enable or diabled for tenant
        if (!$this->tenant->is_enable_sms)
            return 'sms disabled';


        $url = $this->getSmsGateway();

        $data = [
            "api_key" => $this->tenant->sms_api_key,
            "type" => "text",
            "contacts" => $contacts,
            "senderid" => "8801847169884",
            "msg" => $message,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);


        return $response;
    }

    public function sendScheduleSms()
    {

        $scheduleSms = $this->tenant->sms_schedule;

        if ($scheduleSms and is_array($scheduleSms)) {

            $daysOfMonth = [];
            $todaysDay = date("j");


            foreach ($scheduleSms as $item) {
                $daysOfMonth[$item['day_of_month']] = $item['message'];
            }

            if (is_array($daysOfMonth) and in_array($todaysDay, array_keys($daysOfMonth))) {
                $msg = $daysOfMonth[$todaysDay];
                $mobiles = User::where('is_activated', 1)->where('association_id', $this->tenant->id)->pluck('mobile');
                $this->send($mobiles, $msg);
            }
        }
    }
}
