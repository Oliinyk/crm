<?php

namespace App\Services\TicketField\TicketFields;

use App\Models\Layer;
use App\Models\TicketField;
use App\Services\TicketField\TicketFieldDefault;

class TicketFieldLayer extends TicketFieldDefault
{
    /**
     * Show the ticket field.
     *
     * @return mixed
     */
    public function show(): mixed
    {
        return $this->ticket->layer;
    }

    /**
     * Save the ticket field.
     */
    public function save()
    {
        $this->ticket->update(['layer_id' => $this->newValue()]);
    }

    /**
     * Get new value.
     *
     * @return mixed
     */
    public function newValue(): mixed
    {
        $value = $this->ticketField->value;

        if (! is_array($value)) {
            $value = json_decode($value);
        }

        if (is_null($value)) {
            return null;
        }
        
        return collect($value)->filter()->first()['id'];
    }

    /**
     * Add activity log for the ticket field.
     *
     * @return array
     */
    protected function log(): array
    {
        return [
            'type' => TicketField::TYPE_LAYER,
            'name' => $this->ticketField->name,
            'old' => $this->oldValue() ? Layer::find($this->oldValue())?->title : null,
            'new' => $this->newValue() ? Layer::find($this->newValue())?->title : null,
        ];
    }
}