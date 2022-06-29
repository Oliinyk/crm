<?php

namespace App\Http\Resources;

use App\Models\TicketField;
use App\Services\TicketField\TicketFieldProvider;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class TicketFieldResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'order' => $this->order,
            'type' => $this->type,
            'selected' => in_array($this->type, TicketField::defaultTypes()),
            'value' => (new TicketFieldProvider($this->resource))->show(),
        ];
    }
}
