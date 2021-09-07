<?php

namespace Techpanda\Core\Widgets;

use Backend\Classes\WidgetBase;
use Flash;
use Backend\Models\User;
use Renatio\DynamicPDF\Classes\PDF; // import facade
use Techpanda\Core\Classes\Helper;

class ExportAddress extends WidgetBase
{
    /**
     * @var string A unique alias to identify this widget.
     */
    protected $defaultAlias = 'exportaddress';


    public function render()
    {
        return $this->makePartial('default');
    }

    public function onExportAddress()
    {
        if (
            ($bulkAction = post('action')) &&
            ($checkedIds = post('checked')) &&
            is_array($checkedIds) &&
            count($checkedIds)
        ) {


            $templateCode = 'export-address';
            $type = $bulkAction;
            $data = [];
            $userIds = $checkedIds;
            $users = User::with('profile')->whereIn('id', $userIds)->where('is_activated', 1)->get();

            foreach ($users as $user) {

                $data[] = Helper::address($user, $type);
            }
            $fileName = rand() . '.pdf';
            $path = storage_path('temp/public/') . $fileName;
            $save = PDF::loadTemplate($templateCode, ['users' => $data])->save($path);

            return redirect('/storage/temp/public/' . $fileName);
        }
    }
}
