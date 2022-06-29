<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class IndexTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }

    /**
     * Handle a passed validation attempt.
     *
     * @return void
     */
    protected function passedValidation()
    {
        $this->merge([
            'assignee' => $this->getOrArray('assignee'),
            'layer' => $this->getOrArray('layer'),
            'watchers' => $this->getOrArray('watchers'),
            'types' => $this->getOrArray('types'),
        ]);
    }

    public function getOrArray($key)
    {
        return $this->has($key) ? $this->get($key) : [];
    }
}
