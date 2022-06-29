<?php

namespace App\Services\TicketField\TicketFields;

use App\Models\TicketField;
use App\Services\TicketField\TicketFieldCustom;

class TicketFieldDecimal extends TicketFieldCustom
{
    /**
     * Add activity log for the ticket field.
     *
     * @return array
     */
    protected function log():array
    {
        return [
            'type' => TicketField::TYPE_DECIMAL,
            'name' => $this->ticketField->name,
            'old' => $this->oldValue(),
            'new' => $this->newValue(),
        ];
    }
}