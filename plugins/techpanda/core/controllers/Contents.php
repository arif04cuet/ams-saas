<?php namespace Techpanda\Core\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Contents extends Controller
{
   
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController'
    ];

    public $formConfig = 'config_content_form.yaml';

    public $listConfig = [

        'contents' => 'config_content_list.yaml',
        'categories' => 'config_category_list.yaml'
    ];




    public $requiredPermissions = [
        'techpanda.core.manage_contents' 
    ];

    public function __construct()
    {
        $this->vars['mode'] = false;

        if (post('mode')) {
            $mode = post('mode');
            $this->vars['mode'] = $mode;
            $this->formConfig = 'config_' . $mode . '_form.yaml';
        }


        parent::__construct();
        BackendMenu::setContext('Techpanda.Core', 'main-menu-mis', 'side-menu-content');
    }



    public function index()
    {
        $this->asExtension('ListController')->index();
        $this->bodyClass = 'compact-container';
    }

   
    public function onCreateForm()
    {
        $this->asExtension('FormController')->create();
        return $this->makePartial('create_form');
    }

    public function onCreate()
    {
        $this->asExtension('FormController')->create_onSave();
        return $this->refreshLists();
    }

    public function onUpdateForm()
    {
        $this->asExtension('FormController')->update(post('record_id'));
        $this->vars['recordId'] = post('record_id');
        return $this->makePartial('update_form');
    }

    public function onUpdate()
    {
        $this->asExtension('FormController')->update_onSave(post('record_id'));
        return $this->refreshLists();
    }

    public function onDelete()
    {
        $this->asExtension('FormController')->update_onDelete(post('record_id'));
        return $this->refreshLists();
    }

    public function refreshLists()
    {

        return array_merge(

            $this->listRefresh('contents'),
            $this->listRefresh('categories')
        );
    }



}
