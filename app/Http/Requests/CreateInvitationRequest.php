<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CreateInvitationRequest extends FormRequest
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
            'email' => [
                'required',
                'email',
//                Rule::unique('invitations', 'email')
//                    ->where('workspace_id', $this->workspace->id),
            ],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param Validator $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            /**
             * Check if current user is a member of current workspace.
             */
            if ($this->workspace->members()->whereEmail($this->get('email'))->exists()) {
                $validator->errors()->add('email', 'User already exists in this workspace.');
            }
        });
    }
}
