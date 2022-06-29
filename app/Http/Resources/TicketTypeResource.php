<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class TicketTypeResource extends JsonResource
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
            'title' => $this->title,
            'author' => $this->whenLoaded('author', function () {
                return $this->author->name;
            }),
            'updated_at' => $this->updated_at->toDateString(),
            'deleted_at' => $this->deleted_at,
            'fields' => $this->whenLoaded('ticketFields', TicketFieldResource::collection($this->ticketFields)),
        ];
    }
}
