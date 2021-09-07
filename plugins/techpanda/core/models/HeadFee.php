<?php

namespace Techpanda\Core\Models;

use Illuminate\Validation\Rule;
use Model;

/**
 * Model
 */
class HeadFee extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'techpanda_core_head_fees';

    /**
     * @var array Validation rules
     */
    public $rules = [];
    public $belongsTo = [

        'head' => 'Techpanda\Core\Models\AccountHead'
    ];

    public $customMessages  =   [

        'year.fiscalyear' => 'Fiscal year must be in format like 2020-2021',
        'year.unique' => 'This account head already been setup for this fiscal year'
    ];

    //scopes

    public function scopeLatestValue($query, $code, $fiscal_year = null)
    {

        return $query->whereHas('head', function ($q) use ($code) {
            $q->where('code', $code);
        })->when($fiscal_year, function ($q) use ($fiscal_year) {
            return $q->where('year', $fiscal_year);
        })->orderBy('year', 'desc');
    }

    public function beforeValidate()
    {

        $this->rules['year'] = [
            'required',
            'fiscalyear',
            Rule::unique($this->table)
                ->ignore($this->id)
                ->where('head_id', $this->head_id)
        ];


        $this->rules['head_id'] = 'required';
        $this->rules['fee'] = 'required';
    }


    public function getYearAttribute($year)
    {

        $fiscalYear = date("Y") . '-' . (date("Y") + 1);
        return $this->exists ? $year : $fiscalYear;
    }

    //functions

    public function getMonthOptions()
    {
        $months = ["all" => "All"];
        for ($i = 1; $i <= 12; $i++) {

            $months[$i] = date('F', mktime(0, 0, 0, $i, 1));
        }
        return $months;
    }
}
