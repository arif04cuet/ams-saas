<?php

namespace Techpanda\Core\Models;

use Model;

/**
 * UserProfile Model
 */
class UserProfile extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public const DEFAULT_PASSWORD = '12345678';
    /**
     * @var string The database table used by the model.
     */
    public $table = 'techpanda_core_user_profiles';

    /**
     * @var array Guarded fields
     */
    protected $guarded = [];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Validation rules for attributes
     */
    public $rules = [];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [];

    /**
     * @var array Attributes to be cast to JSON
     */
    protected $jsonable = ['kids'];

    /**
     * @var array Attributes to be appended to the API representation of the model (ex. toArray())
     */
    protected $appends = [];

    /**
     * @var array Attributes to be removed from the API representation of the model (ex. toArray())
     */
    protected $hidden = [];

    /**
     * @var array Attributes to be cast to Argon (Carbon) instances
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];

    public $belongsTo = [

        'user' => 'Backend\Models\User'

    ];

    public static function getFromUser($user)
    {

        if ($user->profile)
            return $user->profile;

        $profile = new static;
        $profile->user = $user;
        $profile->save();

        $user->profile = $profile;

        return $profile;
    }
}
