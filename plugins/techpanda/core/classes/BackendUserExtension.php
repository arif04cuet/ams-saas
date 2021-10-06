<?php

namespace Techpanda\Core\Classes;

use App;
use Backend\Controllers\Users as BackendUsersController;
use Backend\Models\User;
use Backend\Models\UserRole;
use BackendAuth;
use Log;
use Flash;
use Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Mail;
use October\Rain\Auth\AuthException;
use Techpanda\Core\Models\AccountHead;
use Techpanda\Core\Models\Transaction;
use Techpanda\Core\Models\UserProfile;
use Techpanda\Core\Scopes\Association;
use Techpanda\Core\Widgets\ExportAddress;

use function Matrix\trace;

class BackendUserExtension
{


    public function boot()
    {
        $this->addFieldstoForm();
    }

    public function addFieldstoForm()
    {

        User::extend(function ($model) {


            //relations
            $model->hasOne['profile'] = ['Techpanda\Core\Models\UserProfile'];

            $model->belongsTo['association'] = ['Techpanda\Core\Models\Association'];

            $model->bindEvent('model.beforeCreate', function () use ($model) {

                $tenantField = Helper::getTenantField();
                $model->{$tenantField} = Helper::getAssociationId();

                //add member role
                if (!$model->role_id)
                    $model->role_id = UserRole::where('code', 'member')->first()->id;

                // activate user immediately when created from backend
                if (App::runningInBackend())
                    $model->is_activated = 1;
            });

            $model->bindEvent('model.beforeUpdate', function () use ($model) {
                //initial  vaue can't be change
                //unset($model->initial_balance);
            });


            //dynamic function

            $model->addJsonable('initial_balance');
            $model->addDynamicMethod('getHeadOptions', function () use ($model) {

                return AccountHead::whereHas('headfees')->get()->pluck('name', 'code');
            });

            //events
            $model->bindEvent('model.form.filterFields', function ($formWidget, $fields, $context) use ($model) {
            });

            // $model->addDynamicMethod('totalDeposit', function () use ($model) {
            //     $loggedUser = BackendAuth::getUser();
            //     $transactions = Transaction::where('user_id', $loggedUser->id)
            //         ->select([DB::raw("sum(amount) as total_amount"), 'user_id'])
            //         ->groupBy('user_id')
            //         ->first();

            //     return number_format($transactions->total_amount);
            // });

            // $model->addDynamicMethod('totalShare', function () use ($model) {
            //     $loggedUser = BackendAuth::getUser();
            //     $transactions = Transaction::where('user_id', $loggedUser->id)
            //         ->select([DB::raw("sum(amount) as total_amount"), 'user_id'])
            //         ->groupBy('user_id')
            //         ->first();

            //     return number_format($transactions->total_amount);
            // });
        });


        BackendUsersController::extend(function ($controller) {


            // add custom behaviors
            if (!$controller->isClassExtendedWith('Techpanda.Core.Behaviors.BackendUserTabController')) {
                $controller->implement[] = 'Techpanda.Core.Behaviors.BackendUserTabController';
            }

            // add roll management behaviors
            if (!$controller->isClassExtendedWith('Techpanda.Core.Behaviors.BackendUserRollController')) {
                $controller->implement[] = 'Techpanda.Core.Behaviors.BackendUserRollController';
            }

            //add customview path
            $controller->addViewPath('$/techpanda/core/controllers/members');

            //add bulk actions method
            $exportlist = new ExportAddress($controller);
            $exportlist->bindToController();

            //association wise list view
            $controller->listConfig = [
                'members' => '$/techpanda/core/controllers/members/config_list.yaml',
                'applications' => '$/techpanda/core/controllers/members/applications/config_list.yaml',
                'associates' => '$/techpanda/core/controllers/members/associates/config_list.yaml',
                'disabled_members' => '$/techpanda/core/controllers/members/disabled/config_list.yaml',
                'member_rolls' => '$/techpanda/core/controllers/members/rolls/config_list.yaml',
            ];

            //association wise form view
            $formConfigPath = post('mode') == 'member_rolls' ? '$/techpanda/core/controllers/members/rolls/config_form.yaml' : '$/techpanda/core/controllers/members/config_form.yaml';
            $controller->formConfig = $formConfigPath;
        });


        BackendUsersController::extendFormFields(function ($form, $model, $context) {

            if (!$model instanceof User)
                return;


            if (!$model->exists)
                return;


            UserProfile::getFromUser($model);
        });
    }
}
