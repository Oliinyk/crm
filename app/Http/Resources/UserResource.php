<?php

namespace App\Http\Resources;

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
            'full_name' => $this->full_name,
            'email' => $this->email,
            'deleted_at' => $this->deleted_at,
            'locale' => $this->locale,
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'image' => $this->whenLoaded('media', function () {
                return [
                    'url' => $this->getFirstMediaUrl(),
                    'id' => $this->getFirstMedia()?->id,
                    'thumb' => $this->getFirstMediaUrl(),
                    'name' => $this->getFirstMedia()?->file_name,
                    'size' => $this->getFirstMedia()?->size,
                    'type' => $this->getFirstMedia()?->mime_type,
                ];
            }),
            'workspaces' => WorkspaceResource::collection($this->whenLoaded('workspaces')),
            'invitations' => InvitationResource::collection($this->whenLoaded('invitations')),
            'notifications' => $this->whenLoaded('notifications')
        ];
    }
}
