<?php

namespace App\Rules;

use App\Models\TicketField;
use Illuminate\Contracts\Validation\Rule;

class DefaultFieldsTypeMustBeDistinctRule implements Rule
{
    /**
     * @var
     */
    public $attribute;

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->attribute = $attribute;
        /**
         * Check if the value exists in the default types.
         */
        if (in_array($value, TicketField::defaultTypes())) {

            /**
             * Get a duplicate ticket types.
             */
            $duplicateTypes = collect(request()->fields)->pluck('type')->duplicates();

            /**
             * Check if the duplicate types have a current value.
             */
            return ! $duplicateTypes->contains($value);
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The '.$this->attribute.' field has a duplicate value.';
    }
}
