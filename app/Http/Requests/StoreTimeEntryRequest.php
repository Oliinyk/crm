<?php

namespace App\Http\Requests;

use App\Enums\TimeEntryTypeEnum;
use Carbon\CarbonInterval;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreTimeEntryRequest extends FormRequest
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
            'date' => [
                'required',
                'date'
            ],
            'description' => [
                'required'
            ],
            'time' => [
                'required',
                'regex:/((?<days>\d+)d)? ?((?<hours>\d{1,2})h)? ?((?<minutes>\d{1,2})m)?/',
                function ($attribute, $value, $fail) {
                    if (is_null(CarbonInterval::make($value))) {
                        $fail('The '.Str::replace('_', ' ', $attribute).' format is invalid.');
                    }
                },
            ]
        ];
    }

    protected function passedValidation()
    {
        $this->merge([
            'time' => $this->time,
            'ticket_id' => $this->ticket->id,
            'workspace_id' => $this->workspace->id,
            'author_id' => $this->user()->id,
            'created_at' => $this->date,
            'type' => TimeEntryTypeEnum::SPENT->value
        ]);
    }
}
