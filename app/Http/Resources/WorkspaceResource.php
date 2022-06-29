<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class WorkspaceResource extends JsonResource
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
            'plan' => $this->plan,
            'image' => $this->whenLoaded('media', function () {
                return [
                    'url' => $this->getFirstMediaUrl(),
                    'thumb' => $this->getFirstMediaUrl(),
                    'name' => $this->getFirstMedia()?->file_name,
                    'id' => $this->getFirstMedia()?->id,
                    'size' => $this->getFirstMedia()?->size,
                    'type' => $this->getFirstMedia()?->mime_type,
                ];
            }),
        ];
    }
}
