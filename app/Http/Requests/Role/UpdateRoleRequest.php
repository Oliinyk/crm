<?php

namespace App\Http\Requests\Role;

use App\Enums\PermissionsEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
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
            'name' => [
                'required',
                'max:50',
                Rule::unique('roles')
                    ->where('workspace_id', $this->workspace->id)
                    ->ignore($this->role->id),
            ],
            'see_projects' => [
                Rule::in([
                    PermissionsEnum::SEE_ALL_PROJECTS->value,
                    PermissionsEnum::SEE_JOINED_PROJECTS->value,
                ]),
                'nullable',
            ],
            'delete_projects' => [
                Rule::in([
                    PermissionsEnum::DELETE_ALL_PROJECTS->value,
                    PermissionsEnum::DELETE_OWN_PROJECTS->value,
                ]),
                'nullable',
            ],
            'create_projects' => [
                Rule::in([
                    PermissionsEnum::CREATE_PROJECTS->value,
                ]),
                'nullable',
            ],
            'edit_all_projects' => [
                Rule::in([
                    PermissionsEnum::EDIT_ALL_PROJECTS->value,
                ]),
                'nullable',
            ],
            'manage_groups' => [
                Rule::in([
                    PermissionsEnum::MANAGE_GROUPS->value,
                ]),
                'nullable',
            ],
            'see_clients' => [
                Rule::in([
                    PermissionsEnum::SEE_CLIENTS->value,
                ]),
                'nullable',
            ],
            'add_clients' => [
                Rule::in([
                    PermissionsEnum::ADD_CLIENTS->value,
                ]),
                'nullable',
            ],
            'delete_clients' => [
                Rule::in([
                    PermissionsEnum::DELETE_CLIENTS->value,
                ]),
                'nullable',
            ],
            'see_roles' => [
                Rule::requiredIf($this->user()->hasRole($this->role)),
                Rule::in([
                    PermissionsEnum::SEE_ROLES->value,
                    null,
                ]),
                'nullable',
            ],
            'add_roles' => [
                Rule::requiredIf($this->user()->hasRole($this->role)),
                Rule::in([
                    PermissionsEnum::ADD_ROLES->value,
                    null,
                ]),
            ],
            'delete_roles' => [
                Rule::requiredIf($this->user()->hasRole($this->role)),
                Rule::in([
                    PermissionsEnum::DELETE_ROLES->value,
                    null,
                ]),
            ],
            'manage_ticket_types' => [
                Rule::in([
                    PermissionsEnum::MANAGE_TICKET_TYPES->value,
                ]),
                'nullable',
            ],
            'see_tickets' => [
                Rule::in([
                    PermissionsEnum::SEE_ALL_TICKETS->value,
                    PermissionsEnum::SEE_JOINED_TICKETS->value,
                ]),
                'nullable',
            ],
            'create_tickets' => [
                Rule::in([
                    PermissionsEnum::CREATE_TICKETS->value,
                ]),
                'nullable',
            ],
            'edit_all_tickets' => [
                Rule::in([
                    PermissionsEnum::EDIT_ALL_TICKETS->value,
                    PermissionsEnum::EDIT_ASSIGNEE_TICKETS->value,
                ]),
                'nullable',
            ],
            'delete_tickets' => [
                Rule::in([
                    PermissionsEnum::DELETE_ALL_TICKETS->value,
                    PermissionsEnum::DELETE_OWN_TICKETS->value,
                ]),
                'nullable',
            ],
            'manage_project_members' => [
                Rule::in([
                    PermissionsEnum::MANAGE_PROJECT_MEMBERS->value,
                ]),
                'nullable',
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
        $this->merge([
            'permissions' => $this->safe()
                ->collect()
                ->except(['name'])
                ->values()
                ->filter()
                ->toArray(),
        ]);
    }
}
