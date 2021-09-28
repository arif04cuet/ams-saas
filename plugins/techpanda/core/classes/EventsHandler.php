<?php

namespace Techpanda\Core\Classes;

use Session;
use BackendAuth;
use Backend\Facades\Backend;
use Backend\Models\User;
use Event;
use Mail;
use October\Rain\Auth\AuthException;
use Techpanda\Core\Controllers\Association as ControllersAssociation;
use Techpanda\Core\Models\AccountHead;
use Techpanda\Core\Models\Association;
use Techpanda\Core\Models\BankBranch;
use Techpanda\Core\Models\HeadFee;
use Techpanda\Core\Models\Transaction;
use Yaml;

class EventsHandler
{

    public function subscribe($events)
    {

        $events->listen('backend.page.beforeDisplay', 'Techpanda\Core\Classes\EventsHandler@beforeDisplay');
        $events->listen('backend.user.login', 'Techpanda\Core\Classes\EventsHandler@afterLogin');
        $events->listen('backend.list.extendQuery', 'Techpanda\Core\Classes\EventsHandler@extendListQuery');
        $events->listen('backend.form.extendFields', 'Techpanda\Core\Classes\EventsHandler@adddynamicFieldstoTnxForm');
        $events->listen('backend.list.injectRowClass', 'Techpanda\Core\Classes\EventsHandler@injectRowClass');
        $events->listen('member.approved', 'Techpanda\Core\Classes\EventsHandler@memberApprovedByAdmin');
    }

    public function memberApprovedByAdmin($user)
    {
        // send emails when member approved
        $data = $user->toArray();
        $data['fullName'] = $user->full_name;

        $template = $user->role->code == 'member' ? 'techpanda.core::mail.after_approved_member' : 'techpanda.core::mail.after_approved_associate_member';

        Mail::send($template, $data, function ($message) use ($user) {
            $message->to($user->email, $user->full_name);
        });
    }
    public function injectRowClass($listWidget, $record, $value)
    {

        // color associate row
        if (get_class($listWidget->getController()) == 'Backend\Controllers\Users' && $listWidget->alias == 'associates') {
            if (!$record->is_activated)
                return   'negative';
            else
                return 'positive';
        }
    }

    public function adddynamicFieldstoTnxForm($widget)
    {

        // You should always check to see if you're extending correct model/controller
        if (!$widget->model instanceof Transaction) {
            return;
        }

        // get form yaml files for transaction model
        $path    = plugins_path() . '/techpanda/core/models/transaction/fields.yaml';
        $old = Yaml::ParseFile($path);

        $newFields = [

            'fees' => [

                'label'   => 'Fees Details',
                'span' => 'full',
                'type'    => 'section'
            ],

            'fiscal_year' => [

                'label'   => 'Fiscal Year',
                'span'    => 'full',
                'type'    => 'dropdown',
                'required' => 1
            ],
            'account_heads' => [

                'label'   => 'Account Heads',
                'span'    => 'full',
                'type'    => 'checkboxlist',
                'cssClass' => 'inline-options',
                'required' => 1,
                'dependsOn' => ['fiscal_year'],
            ]
        ];

        //get account heads
        $heads = AccountHead::get();
        $totalDependsOn = [];

        foreach ($heads as $head) {


            $code = $head->code;
            $fee = HeadFee::latestValue($code)->first();

            $totalDependsOn[] = $code;
            $field = [

                'label' => $head->name,
                'span' => 'auto',
                'dependsOn' => ['account_heads', 'fiscal_year'],
                'required' => 1,
                'hidden' => 1,
                'type' => 'number',
                'default' => 1
            ];

            if ($fee)
                $field['comment'] = 'Unit value: ' . $fee->fee . ' TK';

            if ($code == AccountHead::getSavingHeadName()) {
                $field['label'] = 'Select Month ( already paid months will not be shown)';
                $field['type'] = 'checkboxlist';
                $field['span'] = 'full';
                $field['quickselect'] = false;
                $field['cssClass'] = 'inline-options';
            }

            $newFields[$code] = $field;
        }

        //total field
        $newFields['total'] = [

            'label' => 'Total Tk.',
            'span' => 'left',
            'dependsOn' => $totalDependsOn,
            'readOnly' => 1

        ];

        //remove all file fields
        foreach (array_keys($old['fields']) as $field)
            $widget->removeField($field);

        $widget->addFields($newFields);

        //re-add all file fields
        $widget->addFields($old['fields']);

        $totalDependsOn[] = '_payment_mode';
        $totalDependsOn[] = '_payment_method';
        //add grand total field for online paymant
        $grandtotal['_payment_method'] = [
            'label' => ' Payment Method',
            'span' => 'left',
            'type' => 'dropdown',
            'trigger' => [
                'action' => 'show',
                'field' => '_payment_mode',
                'condition' => 'value[online]',
            ]

        ];

        $grandtotal['_total_with_charge'] = [

            'span' => 'left',
            'dependsOn' => $totalDependsOn,
            'readOnly' => 1,
            'trigger' => [
                'action' => 'show',
                'field' => '_payment_mode',
                'condition' => 'value[online]',
            ]

        ];
        $widget->addFields($grandtotal);

        //$widget->getField('_payment_mode')->default = 'offline';
        $widget->addViewPath(['$/techpanda/core/models/transaction/partial/']);
    }

    public function extendListQuery($listWidget, $query)
    {
        //filter members
        if ($listWidget->model instanceof User) {

            $query->where('association_id', Helper::getAssociationId());
        }

        // filters members/online applications/associates member data in members menu
        if (get_class($listWidget->getController()) === 'Backend\Controllers\Users') {

            // active members
            if ($listWidget->alias === 'members') {

                $query->where('is_activated', 1);
                $query->whereIn('role_id', [1, 2]);
            }

            // online applications
            if ($listWidget->alias === 'applications') {

                $query->where('is_activated', 0);
                $query->whereNull('deleted_at');
                $query->where('role_id', 1);
                $query->orderBy('created_at', 'desc');
            }

            //associate members

            if ($listWidget->alias === 'associates') {

                $query->where('role_id', 3)->orderBy('is_activated', 'asc');
            }
        }
    }
    public function isAdminUser($controller, $action)
    {
        return BackendAuth::getUser() &&
            BackendAuth::getUser()->is_superuser &&
            $action != 'signout' &&
            (!$controller instanceof ControllersAssociation && empty(Helper::getAssociationId()));
    }
    public function beforeDisplay($controller, $action, $params)
    {

        //inject total count to members list
        if (get_class($controller) === 'Backend\Controllers\Users' && $action === 'index') {
            $controller->vars['member_total'] = User::where('association_id', Helper::getAssociationId())->where('is_activated', 1)->whereIn('role_id', [1, 2])->get()->count();
            $controller->vars['application_total'] = User::where('association_id', Helper::getAssociationId())->where('is_activated', 0)->where('role_id', 1)->get()->count();
            $controller->vars['associate_total'] = User::where('association_id', Helper::getAssociationId())->where('role_id', 3)->get()->count();
        }

        //redirect to users page if super admin not belonging to any association
        if ($this->isAdminUser($controller, $action)) {
            return Backend::redirect('techpanda/core/association');
        }


        if (BackendAuth::getUser())
            if (!BackendAuth::getUser()->hasAccess('manage_widgets')) {
                $controller->addCss('/plugins/techpanda/core/assets/css/style.css', '1.1.0');
            }


        if ($controller instanceof \Backend\Controllers\Users) {
            $controller->addJs('/plugins/techpanda/core/assets/js/bulk-actions.js');
        }

        // add datatable plugin to dashboard
        if ($controller instanceof \Backend\Controllers\Index) {
            $controller->addJs('https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js');
            //$controller->addCss('https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css', '1.1.0');

        }
    }
    /**
     * Handle user login events.
     */
    public function afterLogin($user)
    {
        if (!$user->is_activated) {
            $login = $user->getLogin();
            BackendAuth::logout();
            throw new AuthException(sprintf(
                'Cannot login user "%s" as they are not activated.',
                $login
            ));
        }

        $role = $user->role ? $user->role->code : null;
        $userData =
            [
                'id' => $user['id'],
                'name' => $user['first_name'] . ' ' . $user['last_name'],
                'role' => $role,
                'association_id' => $user['association_id']
            ];

        Session::put('user', $userData);
    }
}
