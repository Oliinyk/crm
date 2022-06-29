<?php

namespace App\Http\Requests\Project;

use App\Enums\PermissionsEnum;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class ProjectMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->hasPermissionTo(PermissionsEnum::MANAGE_PROJECT_MEMBERS->value);
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
            'members.*' => [
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
}
