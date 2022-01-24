<?php

namespace Techpanda\Core\Traits;

use Techpanda\Core\Classes\Helper;
use Techpanda\Core\Scopes\Association;

trait ListPopup
{


    public function onReorderForm()
    {
        $this->asExtension('ReorderController')->reorder();
        return $this->makePartial('reorder');
    }

    public function onReorderSave()
    {
        return $this->listRefresh($this->vars['mode']);
    }


    public function onCreateForm()
    {
        $this->asExtension('FormController')->create();
        return $this->makePartial('create_form');
    }

    public function onCreate()
    {
        $this->asExtension('FormController')->create_onSave();
        return $this->listRefresh($this->vars['mode']);
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
        return $this->listRefresh($this->vars['mode']);
    }

    public function onDelete()
    {
        $this->asExtension('FormController')->update_onDelete(post('record_id'));
        return $this->listRefresh($this->vars['mode']);
    }
}
