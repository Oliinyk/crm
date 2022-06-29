<?php

namespace App\Services\TicketField\TicketFields;

use App\Models\TicketField;
use App\Services\TicketField\TicketFieldCustom;

class TicketFieldShortText extends TicketFieldCustom
{
    /**
     * Add activity log for the ticket field.
     *
     * @return mixed
     */
    protected function log(): array
    {
        return [
            'type' => TicketField::TYPE_SHORT_TEXT,
            'name' => $this->ticketField->name,
            'old' => $this->oldValue(),
            'new' => $this->newValue(),
        ];
    }
}