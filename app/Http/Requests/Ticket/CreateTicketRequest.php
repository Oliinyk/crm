<?php

namespace App\Http\Requests\Ticket;

use App\Models\Ticket;
use App\Models\TicketField;
use Carbon\CarbonInterval;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Throwable;

class CreateTicketRequest extends FormRequest
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
            'ticket_type' => [
                'required',
                Rule::exists('ticket_types','id')
                    ->where('workspace_id', $this->workspace->id),
            ],
            'title' => [
                'required',
                'max:50'
            ],
            'fields' => [
                'array',
            ],
            'fields.*.type' => [
                'required',
                Rule::in(TicketField::ticketTypes()),
                Rule::exists('ticket_fields')
                    ->where('workspace_id', $this->user()->workspace_id),
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
        $validator->sometimes('fields.*.value',
            [
                'nullable',
                Rule::in([
                    Ticket::STATUS_OPEN,
                    Ticket::STATUS_IN_PROGRESS,
                    Ticket::STATUS_RESOLVED,
                ]),
            ],
            fn ($input, $item) => $item->type == TicketField::TYPE_STATUS
        );

        $validator->sometimes('fields.*.value',
            [
                'nullable',
                Rule::in([
                    Ticket::PRIORITY_LOW,
                    Ticket::PRIORITY_MEDIUM,
                    Ticket::PRIORITY_HIGH,
                ]),
            ],
            fn ($input, $item) => $item->type == TicketField::TYPE_PRIORITY
        );

        $validator->sometimes('fields.*.value',
            [
                'nullable',
                Rule::exists('tickets', 'id')
                    ->where('workspace_id', $this->user()->workspace_id),
            ],
            fn ($input, $item) => $item->type == TicketField::TYPE_PARENT_TICKET
        );

        $validator->sometimes('fields.*.value',
            [
                'nullable',
                'numeric',
                'between:0,100',
            ],
            fn ($input, $item) => $item->type == TicketField::TYPE_PROGRESS
        );

        $validator->sometimes('fields.*.value',
            [
                'nullable',
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
            fn ($input, $item) => $item->type == TicketField::TYPE_ASSIGNEE
        );

        $validator->sometimes('fields.*.value',
            [
                'nullable',
                'date_format:H:i:s',
            ],
            fn ($input, $item) => $item->type == TicketField::TYPE_TIME
        );

        $validator->sometimes('fields.*.value',
            [
                'nullable',
                'date_format:Y-m-d',
            ],
            fn ($input, $item) => $item->type == TicketField::TYPE_DATE
        );

        $validator->sometimes('fields.*.value',
            [
                'max:50',
            ],
            fn ($input, $item) => $item->type == TicketField::TYPE_SHORT_TEXT
        );

        $validator->sometimes('fields.*.value',
            [
                'max:50',
            ],
            fn ($input, $item) => $item->type == TicketField::TYPE_LONG_TEXT
        );

        $validator->sometimes('fields.*.value',
            [
                'nullable',
                'integer',
            ],
            fn ($input, $item) => $item->type == TicketField::TYPE_NUMERAL
        );

        $validator->sometimes('fields.*.value',
            [
                'nullable',
                'numeric',
            ],
            fn ($input, $item) => $item->type == TicketField::TYPE_DECIMAL
        );

        $validator->sometimes('fields.*.value',
            [
                'nullable',
                'boolean',
            ],
            fn ($input, $item) => $item->type == TicketField::TYPE_CHECKBOX
        );

        $validator->sometimes('fields.*.value',
            [
                'nullable',
                'date_format:Y-m-d',
            ],
            fn ($input, $item) => $item->type == TicketField::TYPE_START_DATE
        );

        $validator->sometimes('fields.*.value',
            [
                'nullable',
                'date_format:Y-m-d',
            ],
            fn ($input, $item) => $item->type == TicketField::TYPE_DUE_DATE
        );

        $validator->sometimes('fields.*.value',
            [
                'nullable',
                'regex:/((?<days>\d+)d)? ?((?<hours>\d{1,2})h)? ?((?<minutes>\d{1,2})m)?/',
                function ($attribute, $value, $fail) {
                    if (is_null(CarbonInterval::make($value))) {
                        $fail('The '.Str::replace('_', ' ', $attribute).' format is invalid.');
                    }
                },
            ],
            fn ($input, $item) => $item->type == TicketField::TYPE_ESTIMATE
        );

        $validator->sometimes('fields.*.value',
            [
                'nullable',
                'integer',
                'min:0',
            ],
            fn ($input, $item) => $item->type == TicketField::TYPE_TIME_SPENT
        );

        $validator->sometimes('fields.*.value',
            [
                'nullable',
                Rule::exists('layers', 'id')
                    ->where('workspace_id', $this->user()->workspace_id),
            ],
            fn ($input, $item) => $item->type == TicketField::TYPE_LAYER
        );

        $validator->sometimes('fields.*.value.*',
            [
                Rule::exists('users', 'id'),
                function ($attribute, $value, $fail) {
                    if ($this->workspace->members()->whereId($value)->doesntExist()) {
                        $fail('The '.$attribute.' is invalid.');
                    }
                },
            ],
            function (Fluent $input, $item) {
                return collect($input->fields)
                        ->filter(fn ($data) => ! is_null($data['value']) && is_array($data['value']) && in_array($item,
                                $data['value']))
                        ->first()['type'] == TicketField::TYPE_WATCHERS;
            }
        );

        $validator->sometimes('fields.*.value',
            [
                'nullable',
                'array',
            ],
            fn ($input, $item) => $item->type == TicketField::TYPE_WATCHERS
        );
    }

    /**
     * Handle a passed validation attempt.
     *
     * @return void
     */
    protected function passedValidation()
    {
        $this->merge([
            'fields' => collect($this->fields)
                ->map(fn ($data, $index) => [
                    'workspace_id' => $this->user()->workspace_id,
                    'order' => $index + 1,
                    'type' => $data['type'],
                    'name' => $data['name'],
                    'value' => is_array($data['value']) ? json_encode($data['value']) : $data['value'],
                ])->toArray(),
        ]);
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        try {
            return collect($this->fields)
                ->mapWithKeys(fn ($data, $key) => ["fields.{$key}.value" => $data['name']])
                ->toArray();
        } catch (Throwable $exception) {
            return [];
        }
    }
}
