<?php

namespace Techpanda\Core\Classes;

use System\Classes\ModelBehavior;
use Techpanda\Core\Classes\Helper;
use Techpanda\Core\Scopes\Association;

class TenantModel extends ModelBehavior
{
    public function __construct($model)
    {
        $this->model = $model;

        $model::addGlobalScope(new Association);

        $model::extend(function ($model) {

            $model->belongsTo['association'] = ['Techpanda\Core\Models\Association'];

            $model->bindEvent('model.beforeCreate', function () use ($model) {
                $model->addAssociationId();
            });
        });

     
    }

    public function addAssociationId()
    {
        $tenantColumn = $this->getAssociationColumn();
        $this->model->{$tenantColumn} = Helper::getAssociationId();
    }

    public function getAssociationColumn()
    {
        return 'association_id';
    }
}
