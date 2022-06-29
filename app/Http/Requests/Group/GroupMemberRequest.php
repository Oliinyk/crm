<?php

namespace App\Http\Requests\Group;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class GroupMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('update', $this->group);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'members' => [
                'array',
                'required',
            ],
            'members.*.id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = User::whereId($value)
                        ->whereHas('workspaces', fn ($query) => $query->whereId($this->workspace->id))
                        ->exists();
                    if (! $exists) {
                        $fail(__('The selected member is invalid.'));
                    }
                },
            ],
        ];
    }

    /**
     * Handle a passed validation attempt.
     *
     * @return void
     */
    protected function passedValidation()
    {
        $this->merge(['members' => collect($this->members)->pluck('id')->toArray()]);
    }
}
