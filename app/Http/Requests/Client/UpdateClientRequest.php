<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
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
            'name' => ['required', 'max:50'],
            'status' => ['max:50'],
            'email' => [
                'max:50',
                'email',
                Rule::unique('clients')
                    ->where(fn ($query) => $query->where('workspace_id', $this->workspace->id))
                    ->ignore($this->client->id)
            ],
            'phone' => ['max:50'],
            'city' => ['max:50'],
        ];
    }
}
