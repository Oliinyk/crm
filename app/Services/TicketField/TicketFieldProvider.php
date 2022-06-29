<?php

namespace App\Services\TicketField;

use App\Models\TicketField;
use App\Models\TicketType;
use App\Services\TicketField\TicketFields\TicketFieldAssignee;
use App\Services\TicketField\TicketFields\TicketFieldCheckbox;
use App\Services\TicketField\TicketFields\TicketFieldDate;
use App\Services\TicketField\TicketFields\TicketFieldDecimal;
use App\Services\TicketField\TicketFields\TicketFieldDueDate;
use App\Services\TicketField\TicketFields\TicketFieldEstimate;
use App\Services\TicketField\TicketFields\TicketFieldLayer;
use App\Services\TicketField\TicketFields\TicketFieldLongText;
use App\Services\TicketField\TicketFields\TicketFieldNumeral;
use App\Services\TicketField\TicketFields\TicketFieldParentTicket;
use App\Services\TicketField\TicketFields\TicketFieldPriority;
use App\Services\TicketField\TicketFields\TicketFieldProgress;
use App\Services\TicketField\TicketFields\TicketFieldSeparator;
use App\Services\TicketField\TicketFields\TicketFieldShortText;
use App\Services\TicketField\TicketFields\TicketFieldStartDate;
use App\Services\TicketField\TicketFields\TicketFieldStatus;
use App\Services\TicketField\TicketFields\TicketFieldTime;
use App\Services\TicketField\TicketFields\TicketFieldTimeSpent;
use App\Services\TicketField\TicketFields\TicketFieldWatchers;
use Illuminate\Database\Eloquent\Model;

class TicketFieldProvider
{
    /**
     * @var TicketField
     */
    private TicketField $ticketField;

    /**
     * @var Model
     */
    private Model $ticket;

    /**
     * @var string[]
     */
    public array $ticketFields = [
        TicketField::TYPE_STATUS => TicketFieldStatus::class,
        TicketField::TYPE_LAYER => TicketFieldLayer::class,
        TicketField::TYPE_PARENT_TICKET => TicketFieldParentTicket::class,
        TicketField::TYPE_PROGRESS => TicketFieldProgress::class,
        TicketField::TYPE_ASSIGNEE => TicketFieldAssignee::class,
        TicketField::TYPE_WATCHERS => TicketFieldWatchers::class,
        TicketField::TYPE_START_DATE => TicketFieldStartDate::class,
        TicketField::TYPE_DUE_DATE => TicketFieldDueDate::class,
        TicketField::TYPE_ESTIMATE => TicketFieldEstimate::class,
        TicketField::TYPE_TIME_SPENT => TicketFieldTimeSpent::class,
        TicketField::TYPE_PRIORITY => TicketFieldPriority::class,
        TicketField::TYPE_TIME => TicketFieldTime::class,
        TicketField::TYPE_DATE => TicketFieldDate::class,
        TicketField::TYPE_SHORT_TEXT => TicketFieldShortText::class,
        TicketField::TYPE_NUMERAL => TicketFieldNumeral::class,
        TicketField::TYPE_DECIMAL => TicketFieldDecimal::class,
        TicketField::TYPE_CHECKBOX => TicketFieldCheckbox::class,
        TicketField::TYPE_LONG_TEXT => TicketFieldLongText::class,
        TicketField::TYPE_SEPARATOR => TicketFieldSeparator::class,
    ];

    /**
     * @param TicketField $ticketField
     */
    public function __construct(TicketField $ticketField)
    {
        $this->ticketField = $ticketField;
        $this->ticket =  $ticketField->ticketField;
    }

    /**
     *  Save.
     */
    public function save()
    {
        if ($this->ticketField->ticket_field_type == TicketType::class) {
            return;
        }

        (new $this->ticketFields[$this->ticketField->type]($this->ticket, $this->ticketField))->handle();
    }

    /**
     * Show.
     *
     * @return mixed
     */
    public function show(): mixed
    {
        if ($this->ticketField->ticket_field_type == TicketType::class) {
            return $this->ticketField->value;
        }

        return (new $this->ticketFields[$this->ticketField->type]($this->ticket, $this->ticketField))->show();
    }
}