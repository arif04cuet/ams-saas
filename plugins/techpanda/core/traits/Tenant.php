<?php

namespace Techpanda\Core\Traits;

use Techpanda\Core\Classes\Helper;
use Techpanda\Core\Scopes\Association;

trait Tenant
{

    public static function bootTenant()
    {

        static::addGlobalScope(new Association);
        static::extend(function ($model) {

            //relation to site
            $model->belongsTo['association'] = ['Techpanda\Core\Models\Association'];

            $model->bindEvent('model.beforeCreate', function () use ($model) {
                $model->addAssociationId();
            });
        });
    }

    public function addAssociationId()
    {
        $tenantColumn = $this->getAssociationColumn();
        $this->{$tenantColumn} = Helper::getAssociationId();
    }

    public function getAssociationColumn()
    {
        return  Helper::getTenantField();
    }
}
