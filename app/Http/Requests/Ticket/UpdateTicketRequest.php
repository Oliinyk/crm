<?php

namespace App\Http\Requests\Ticket;

use App\Models\Ticket;
use App\Models\TicketField;
use Carbon\CarbonInterval;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateTicketRequest extends FormRequest
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
            'media' => [
                'array',
            ],
            'title' => [
                'nullable',
            ],
            'id' => [
                Rule::exists('ticket_fields')
                    ->where('workspace_id', $this->workspace->id)
                    ->where('ticket_field_id', $this->ticket->id),
            ],
            'type' => [
                Rule::in(TicketField::ticketTypes()),
                Rule::exists('ticket_fields')
                    ->where('workspace_id', $this->user()->workspace_id)
                    ->where('ticket_field_id', $this->ticket->id)
                    ->where('id', $this->id),
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
        $validator->sometimes('value',
            [
                'nullable',
                Rule::in([
                    Ticket::STATUS_OPEN,
                    Ticket::STATUS_IN_PROGRESS,
                    Ticket::STATUS_RESOLVED,
                ]),
            ],
            fn ($input) => $input->type == TicketField::TYPE_STATUS
        );

        $validator->sometimes('value',
            [
                'nullable',
                Rule::in([
                    Ticket::PRIORITY_LOW,
                    Ticket::PRIORITY_MEDIUM,
                    Ticket::PRIORITY_HIGH,
                ]),
            ],
            fn ($input) => $input->type == TicketField::TYPE_PRIORITY
        );

        $validator->sometimes('value',
            [
                'nullable',
                Rule::exists('tickets', 'id')
                    ->where('workspace_id', $this->workspace->id),
            ],
            fn ($input) => $input->type == TicketField::TYPE_PARENT_TICKET
        );

        $validator->sometimes('value',
            [
                'nullable',
                'numeric',
                'between:0,100',
            ],
            fn ($input) => $input->type == TicketField::TYPE_PROGRESS
        );

        $validator->sometimes('value.id',
            [
                Rule::exists('users', 'id'),
                function ($attribute, $value, $fail) {
                    if ($this->workspace->members()->whereId($value)->doesntExist()) {
                        $fail('The selected '.Str::replace('_', ' ',
                                $attribute).' must be a member of the current workspace.');
                    }
                },
                function ($attribute, $value, $fail) {
                    if ($this->project->members()->whereId($value)->doesntExist()) {
                        $fail('The selected '.Str::replace('_', ' ',
                                $attribute).' must be a member of the current project.');
                    }
                },
            ],
            fn ($input) => $input->type == TicketField::TYPE_ASSIGNEE
        );

        $validator->sometimes('value',
            [
                'date_format:H:i:s',
            ],
            fn ($input) => $input->type == TicketField::TYPE_TIME
        );

        $validator->sometimes('value',
            [
                'date_format:Y-m-d',
            ],
            fn ($input) => $input->type == TicketField::TYPE_DATE
        );

        $validator->sometimes('value',
            [
                'max:50',
            ],
            fn ($input) => $input->type == TicketField::TYPE_SHORT_TEXT
        );

        $validator->sometimes('value',
            [
                'integer',
            ],
            fn ($input) => $input->type == TicketField::TYPE_NUMERAL
        );

        $validator->sometimes('value',
            [
                'numeric',
            ],
            fn ($input) => $input->type == TicketField::TYPE_DECIMAL
        );

        $validator->sometimes('value',
            [
                'boolean',
            ],
            fn ($input) => $input->type == TicketField::TYPE_CHECKBOX
        );

        $validator->sometimes('value',
            [
                'date_format:Y-m-d',
            ],
            fn ($input) => $input->type == TicketField::TYPE_START_DATE
        );

        $validator->sometimes('value',
            [
                'date_format:Y-m-d',
            ],
            fn ($input) => $input->type == TicketField::TYPE_DUE_DATE
        );

        $validator->sometimes('value',
            [
                'nullable',
                'regex:/((?<days>\d+)d)? ?((?<hours>\d{1,2})h)? ?((?<minutes>\d{1,2})m)?/',
                function ($attribute, $value, $fail) {
                    if (is_null(CarbonInterval::make($value))) {
                        $fail('The '.Str::replace('_', ' ', $attribute).' format is invalid.');
                    }
                },
            ],
            fn ($input) => $input->type == TicketField::TYPE_ESTIMATE
        );

        $validator->sometimes('value',
            [
                'nullable',
                'integer',
                'min:0',
            ],
            fn ($input) => $input->type == TicketField::TYPE_TIME_SPENT
        );

        $validator->sometimes('value.id',
            [
                'nullable',
                Rule::exists('layers', 'id')
                    ->where('workspace_id', $this->workspace->id),
            ],
            fn ($input) => $input->type == TicketField::TYPE_LAYER
        );

        $validator->sometimes('value.*.id',
            [
                Rule::exists('users', 'id'),
                function ($attribute, $value, $fail) {
                    if ($this->workspace->members()->whereId($value)->doesntExist()) {
                        $fail('The '.$attribute.' is invalid.');
                    }
                },
            ],
            fn ($input) => $input->type == TicketField::TYPE_WATCHERS
        );

        $validator->sometimes('value',
            'array',
            fn ($input) => $input->type == TicketField::TYPE_WATCHERS
        );
    }
}
