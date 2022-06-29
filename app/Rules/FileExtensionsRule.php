<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Str;

class FileExtensionsRule implements Rule
{
    /**
     * @var string
     */
    private array $extension;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($extension)
    {
        $this->extension = $extension;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (is_array($value) && array_key_exists('url', $value) && !is_null($value['url'])) {
            return Str::endsWith($value['url'], $this->extension);
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
        return 'The file must be a file of type:'.implode(', ', $this->extension);
    }
}
