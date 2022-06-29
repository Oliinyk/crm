<?php

namespace App\Services\TicketField\TicketFields;

use App\Models\TicketField;
use App\Services\TicketField\TicketFieldDefault;

class TicketFieldCheckbox extends TicketFieldDefault
{
    /**
     * Add activity log for the ticket field.
     *
     * @return array
     */
    protected function log():array
    {
        return [
            'type' => TicketField::TYPE_CHECKBOX,
            'name' => $this->ticketField->name,
            'old' => $this->oldValue(),
            'new' => $this->newValue(),
        ];
    }
}