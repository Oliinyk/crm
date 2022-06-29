<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class InvitationResource extends JsonResource
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
            'email' => $this->email,
            'token' => $this->token,
            'author' => new \App\Http\Resources\Search\UserResource($this->author),
            'created_at' => $this->created_at->toDateString(),
            'workspace'=> new WorkspaceResource($this->workspace)
        ];
    }
}
