<?php

namespace Techpanda\Core\Classes;

use Backend\Models\User;
use Techpanda\Core\Models\Association;

class SslCommerceClient
{

    public $sandboxEndPoint =  'https://sandbox.sslcommerz.com';
    public $liveEndPoint =  'https://securepay.sslcommerz.com';


    public function getGatewayUrl()
    {
        $url = $this->sandboxEndPoint;

        if (env('SSL_PAYMENT_MODE') == 'live')
            $url = $this->liveEndPoint;

        return $url;
    }
    public function gwprocess($data)
    {

        $url = $this->getGatewayUrl() . '/gwprocess/v4/api.php';

        return $this->send($url, $data);
    }
    public function send($url, $data, $post = 1)
    {


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, env('SSL_LOCALHOST'));

        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
        }
        curl_close($ch);

        if (isset($error_msg)) {
            $response = $error_msg;
        }

        return $response;
    }
}
