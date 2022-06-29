<?php

namespace App\Http\Resources;

use Carbon\CarbonInterval;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class TimeEntryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        return[
            'id' => $this->id,
            'description' => $this->description,
            'time' => $this->time->cascade()->forHumans([
                'skip' => ['week'],
                'minimumUnit' => 'minute',
            ]),
            'date' => $this->created_at->toDateString(),
            'author'=>  new UserResource($this->author)
        ];
    }
}
