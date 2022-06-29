<?php

namespace App\Http\Resources\Search;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class UserResource extends JsonResource
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
            'name' => $this->full_name,
            'image' => $this->whenLoaded('media', function () {
                return [
                    'url' => $this->getFirstMediaUrl(),
                    'name' => $this->getFirstMedia()?->file_name,
                    'size' => $this->getFirstMedia()?->size,
                    'type' => $this->getFirstMedia()?->mime_type,
                ];
            }),
        ];
    }
}
