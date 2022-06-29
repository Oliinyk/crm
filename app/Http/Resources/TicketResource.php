<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public static $wrap = '';

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'assignee' => new \App\Http\Resources\Search\UserResource($this->assignee),
            'due_date' => $this->due_date?->toDateString(),
            'layer' => $this->layer?->title,
            'parent_ticket' => $this->whenLoaded('parentTicket', new ParentTicketResource($this->parentTicket)),
            'child_tickets' => TicketResource::collection($this->childTickets),
            'watchers' => \App\Http\Resources\Search\UserResource::collection($this->watchers),
            'priority' => $this->priority,
            'progress' => $this->progress,
            'start_date' => $this->start_date?->toDateString(),
            'status' => $this->status,
            'ticket_type' => $this->ticketType?->name,
            'ticket_type_id' => $this->ticketType?->id,
            'time_estimate' => $this->time_estimate,
            'time_spent' => $this->time_spent,
            'title' => $this->title,
            'changed' => $this->updated_at->toDateTimeString(),
            'author' => new UserResource($this->author),
            'fields' => $this->whenLoaded('ticketFields', TicketFieldResource::collection($this->ticketFields->load('ticketField'))),
            'media' => MediaResource::collection($this->getMedia()),
            'comments' => $this->whenLoaded('comments', CommentsResource::collection($this->comments)),
            'activity_log' => ActivityLogResource::collection($this->activities)
        ];
    }
}
