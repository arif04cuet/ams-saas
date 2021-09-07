<?php namespace Techpanda\Core\Models;

use Model;
use Techpanda\Core\Traits\Tenant;

/**
 * Model
 */
class Category extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use Tenant;
    
    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'techpanda_core_content_categories';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'title'=>'required'
    ];

    public $hasMany = [

        'contents'=>['Techpanda\Core\Models\Content']
    ];
}
