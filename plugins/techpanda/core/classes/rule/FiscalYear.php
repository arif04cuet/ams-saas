<?php

use FontLib\Table\Type\name;

namespace Techpanda\Core\Classes\Rule;

use Illuminate\Contracts\Validation\Rule;

class FiscalYear implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (is_array($year = explode('-', $value)) && count($year) == 2) {

            list($from, $to) = $year;

            //return (strlen($from) == 4 and is_numeric($from)) && (strlen($to) == 4 and is_numeric($from));
            return preg_match('/^\d{4}$/', $from) && preg_match('/^\d{4}$/', $to) && ($to == $from + 1);
        }

        return false;
    }

    /**
     * Validation callback method.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array  $params
     * @return bool
     */
    public function validate($attribute, $value, $params)
    {
        return $this->passes($attribute, $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The fiscal year must be in format like xxxx-xxxx';
    }
}
