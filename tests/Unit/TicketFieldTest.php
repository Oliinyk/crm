<?php

namespace Tests\Unit;

use App\Models\TicketField;
use App\Models\TicketType;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketFieldTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_must_have_ticket_field_attribute()
    {
        $ticketField = TicketField::factory()->create();

        $this->assertInstanceOf(TicketType::class, $ticketField->ticketField);
    }

    /**
     * @test
     */
    public function ticket_field_belongs_to_workspace()
    {
        $ticketField = TicketField::factory()->create();

        $this->assertInstanceOf(Workspace::class, $ticketField->workspace);
    }

    /**
     * @test
     */
    public function ticket_field_must_return_all_custom_types()
    {
        $this->assertSame([
            TicketField::TYPE_DATE,
            TicketField::TYPE_TIME,
            TicketField::TYPE_SHORT_TEXT,
            TicketField::TYPE_LONG_TEXT,
            TicketField::TYPE_NUMERAL,
            TicketField::TYPE_DECIMAL,
            TicketField::TYPE_CHECKBOX,
            TicketField::TYPE_SEPARATOR,
        ], TicketField::customTypes());
    }

    /**
     * @test
     */
    public function ticket_field_must_return_default_types()
    {
        $this->assertSame([
            TicketField::TYPE_STATUS,
            TicketField::TYPE_PRIORITY,
            TicketField::TYPE_LAYER,
            TicketField::TYPE_PARENT_TICKET,
            TicketField::TYPE_PROGRESS,
            TicketField::TYPE_ASSIGNEE,
            TicketField::TYPE_WATCHERS,
            TicketField::TYPE_START_DATE,
            TicketField::TYPE_DUE_DATE,
            TicketField::TYPE_ESTIMATE,
            TicketField::TYPE_TIME_SPENT,
        ], TicketField::defaultTypes());
    }

    /**
     * @test
     */
    public function ticket_field_must_return_all_types()
    {
        $this->assertSame([
            TicketField::TYPE_STATUS,
            TicketField::TYPE_PRIORITY,
            TicketField::TYPE_LAYER,
            TicketField::TYPE_PARENT_TICKET,
            TicketField::TYPE_PROGRESS,
            TicketField::TYPE_ASSIGNEE,
            TicketField::TYPE_WATCHERS,
            TicketField::TYPE_START_DATE,
            TicketField::TYPE_DUE_DATE,
            TicketField::TYPE_ESTIMATE,
            TicketField::TYPE_TIME_SPENT,
            TicketField::TYPE_DATE,
            TicketField::TYPE_TIME,
            TicketField::TYPE_SHORT_TEXT,
            TicketField::TYPE_LONG_TEXT,
            TicketField::TYPE_NUMERAL,
            TicketField::TYPE_DECIMAL,
            TicketField::TYPE_CHECKBOX,
            TicketField::TYPE_SEPARATOR,
        ], TicketField::ticketTypes());
    }
}
