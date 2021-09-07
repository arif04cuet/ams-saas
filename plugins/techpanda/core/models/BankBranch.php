<?php namespace Techpanda\Core\Models;

use Model;
use October\Rain\Database\Builder;
use Techpanda\Core\Classes\Helper;

/**
 * Model
 */
class BankBranch extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    protected static function boot()
    {
        parent::boot();
        
        static::addGlobalScope('bank', function (Builder $query) {

            $bank = Helper::getAssociation()->bank;
            $query->when($bank,
            
            function($q) use ($bank){
                return $q->where('bank_id',$bank->id);
            },

            function($q){
                return $q->where('bank_id',0);
            }

          );


        });
    }

    
    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'techpanda_core_bank_branches';

    /**
     * @var array Validation rules
     */
    public $rules = [
        
        'name'=>'required',
        'code'=>'required'
    ];

    public $belongsTo = [

        'bank' => ['Techpanda\Core\Models\Bank']
    ];

    public function beforeCreate()
    {
        if($bank = Helper::getAssociation()->bank)
            $this->bank = $bank;
    }

    public static function getBranchByAtmId($atmId)
    {
        $code = substr($atmId,0,3);
        
        return BankBranch::where('code',$code)->first();
    }
}
