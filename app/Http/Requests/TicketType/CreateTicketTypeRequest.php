<?php

namespace App\Http\Requests\TicketType;

use App\Models\TicketField;
use App\Rules\DefaultFieldsTypeMustBeDistinctRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTicketTypeRequest extends FormRequest
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
                Rule::unique('ticket_types')
                    ->where('workspace_id', $this->workspace->id),
            ],
            'title' => [
                'required',
                'max:50',
            ],
            'fields' => [
                'array',
                'min:1',
            ],
            'fields.*.type' => [
                'required',
                Rule::in(TicketField::ticketTypes()),
                new DefaultFieldsTypeMustBeDistinctRule(),
            ],
            'fields.*.name' => [
                'max:50',
                'required_unless:fields.*.type,'.TicketField::TYPE_SEPARATOR,
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
        /**
         * Transform collection of the ticket fields.
         */
        $field = collect($this->fields)->map(fn ($data, $index) => [
            'workspace_id' => $this->workspace->id,
            'order' => $index + 1,
            'type' => $data['type'],
            'name' => $data['name'],
        ])->toArray();

        $this->merge(['fields' => $field]);
    }
}
