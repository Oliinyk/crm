<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaResource extends JsonResource
{
    /**
     * @var Media
     */
    public $resource = Media::class;

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
            'url' =>  $this->getFullUrl(),
            'thumb' =>  $this->when($this->resource->getTypeFromMime() == 'image', $this->getFullUrl(), null),
            'name' => $this->file_name,
            'size' => $this->size,
            'type' => $this->mime_type,
        ];
    }
}
