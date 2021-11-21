<?php

use Backend\Models\User;
use Illuminate\Support\Facades\DB;
use Techpanda\Core\Classes\SslCommerceClient;
use Techpanda\Core\Models\AccountHead;
use Techpanda\Core\Models\Transaction;
use League\Csv\Reader as CsvReader;
use Maatwebsite\Excel\Facades\Excel;
use Techpanda\Core\Classes\Helper;
use Techpanda\Core\Classes\SmsSender;
use Techpanda\Core\Models\Association;
use Techpanda\Core\Models\MonthlySaving;

Route::get('/backend/test', function () {
    $from = 2020;
    $to = 2021;
    $userId = 50;

    $items = AccountHead::allShareTransactions();



    return $items->toArray();
});

Route::get('/backend/sendsms', function () {

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

    return 'Done!';
});


Route::get('/backend/ssl/start', function () {

    $data = [
        'store_id' => env('STORE_ID'),
        'store_passwd' => 'bcsp65f5da2a2c1379@ssl',
        'total_amount' => 100,
        'currency' => 'BDT',
        'tran_id' => uniqid(),
        'success_url' => 'http://127.0.0.1:8000/backend/ssl/success',
        'fail_url' =>    'http://127.0.0.1:8000/backend/ssl/fail',
        'cancel_url' => 'http://127.0.0.1:8000/backend/ssl/cansel',
        'cus_name' => 'Arif Hossain',
        'cus_email' =>   'arif04cuet@gmail.com',
        'cus_add1' =>    'h#24, r#01',
        'cus_add2' =>    'Adabor, Shyamoly',
        'cus_city' =>    'dhaka',
        'cus_country' => 'Bangladesh',
        'cus_phone' =>   '01717348147',
        'shipping_method' => 'NO',
        'num_of_item' => 2,
        'product_name' =>    'test product 1',
        'product_category' =>   'monthly deposit',
        'product_profile' => 'general'

    ];

    $paymentClient = new SslCommerceClient();

    return json_decode($paymentClient->gwprocess($data), true);
});

// ssl commerce live
$sslprefix = Backend::uri() . '/ssl/';
Route::prefix($sslprefix)->group(function () {

    Route::any('success', function () {
        $data = request();

        if (isset($data['val_id']) and !empty($data['val_id'])) {


            $data = [
                'val_id' => $data['val_id'],
                'store_id' => env('STORE_ID'),
                'store_passwd' => env('STORE_PASSWORD'),
                'v' => 1,
                'format' => 'json'
            ];

            //send request to sslcommerce

            $result = Http::get(Transaction::validationUrl(), function ($http) use ($data) {

                $http->data($data);
                if (!config('app.debug'))
                    $http->verifySSL();
            });


            $sslResponse = json_decode($result->body, true);

            if ($result->code == 200 and $sslResponse['status'] == 'VALID') {

                //update transaction
                $tnxId = $sslResponse['tran_id'];
                $transaction = Transaction::withoutGlobalScopes()->where('tnx_id', $tnxId)->first();
                $transaction->status = Transaction::STATUS_PAID;
                $transaction->save();

                $msg = 'Successfull. thanks for your payment';
                Flash::success($msg);
            } else {
                $msg = 'Something went wrong';
                Flash::error($msg);
            }
        }

        return Backend::redirect('/?type=success');
    });

    Route::any('fail', function () {

        $msg = 'Sms send successfully';
        Flash::success($msg);
        return Backend::redirect('/?type=fail');
    });


    Route::any('cancel', function () {

        $msg = 'Sms send successfully';
        Flash::success($msg);
        return Backend::redirect('/?type=cancel');
    });

    Route::any('ipn', function () {

        $msg = 'Sms send successfully';
        Flash::success($msg);
        return Backend::redirect('/');
    });
});
