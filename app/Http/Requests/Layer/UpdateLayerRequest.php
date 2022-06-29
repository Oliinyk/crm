<?php

namespace App\Http\Requests\Layer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLayerRequest extends FormRequest
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
            'title' => ['required', 'max:50'],
            'image' => ['image', 'nullable'],
            'parent_layer_id' => [
                Rule::exists('layers', 'id')
                    ->where('workspace_id', $this->workspace->id),
            ],
        ];
    }
}
