<?php

namespace Tests\Unit;

use App\Models\Ticket;
use App\Models\TicketField;
use App\Models\TicketType;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTypeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function ticket_type_belongs_to_workspace()
    {
        $ticketType = TicketType::factory()->create();

        $this->assertInstanceOf(Workspace::class, $ticketType->workspace);
    }

    /**
     * @test
     */
    public function ticket_type_belongs_to_the_author()
    {
        $ticketType = TicketType::factory()->create();
        $user = User::factory()->create();

        $ticketType->author()->associate($user);

        $this->assertInstanceOf(User::class, $ticketType->author);
        $this->assertSame($user->id, $ticketType->author->id);
    }

    /**
     * @test
     */
    public function ticket_type_must_have_a_filter_scope()
    {
        TicketType::factory()->count(10)->create();

        $client = TicketType::factory()->create([
            'name' => '123123kek',
        ]);

        $result = TicketType::filter(['search' => '123123kek'])->get();

        $this->assertCount(1, $result);
        $this->assertSame($client->id, $result->first()->id);
    }

    /**
     * @test
     */
    public function ticket_type_has_many_ticket_fields()
    {
        $ticketType = TicketType::factory()->create();

        TicketField::factory()->count(10)->create([
            'ticket_field_type' => TicketType::class,
            'ticket_field_id' => $ticketType->id,
        ]);

        $this->assertInstanceOf(TicketField::class, $ticketType->ticketFields->first());
    }

    /**
     * @test
     */
    public function ticket_type_has_many_default_ticket_fields()
    {
        $ticketType = TicketType::factory()->create();

        TicketField::factory()->count(10)->create([
            'type' => TicketField::TYPE_STATUS,
            'ticket_field_type' => TicketType::class,
            'ticket_field_id' => $ticketType->id,
        ]);

        $this->assertInstanceOf(TicketField::class, $ticketType->defaultTicketFields->first());
        $this->assertSame(10, $ticketType->defaultTicketFields->count());
    }

    /**
     * @test
     */
    public function ticket_type_has_many_custom_ticket_fields()
    {
        $ticketType = TicketType::factory()->create();

        TicketField::factory()->count(10)->create([
            'type' => TicketField::TYPE_SHORT_TEXT,
            'ticket_field_type' => TicketType::class,
            'ticket_field_id' => $ticketType->id,
        ]);

        $this->assertInstanceOf(TicketField::class, $ticketType->customTicketFields->first());
        $this->assertSame(10, $ticketType->customTicketFields->count());
    }

    /**
     * @test
     */
    public function ticket_type_has_many_tickets()
    {
        $ticketType = TicketType::factory()->create();

        Ticket::factory()->count(10)->create([
            'ticket_type_id' => $ticketType->id,
        ]);

        $this->assertInstanceOf(Ticket::class, $ticketType->tickets->first());
    }

    /**
     * @test
     * @dataProvider nameProvider
     */
    public function it_must_generate_new_name($name, $copiedName)
    {
        $user = User::factory()->create();
        auth()->login($user);
        request()->merge(['workspace' => $user->workspaces->first()]);

        TicketType::factory()->create([
            'name' => 'test - copy 10',
            'workspace_id' => $user->workspace_id,
        ]);

        $ticketType = TicketType::factory()->create([
            'name' => $name,
            'workspace_id' => $user->workspace_id,
        ]);

        $this->assertSame($copiedName, $ticketType->generateNewName());
    }

    public function nameProvider()
    {
        return [
            [
                'test',
                'test - copy',
            ],
            [
                'test - copy',
                'test - copy 2',
            ],
            [
                'test - copy 1',
                'test - copy 2',
            ],
            [
                'test - copy 2',
                'test - copy 3',
            ],
            [
                'copy test',
                'copy test - copy',
            ],
            [
                'test    copy',
                'test    copy 2',
            ],
            [
                'test    copy 2',
                'test    copy 3',
            ],
            [
                'test - copy 9',
                'test - copy 11',
            ],
            [
                'test - copy 10',
                'test - copy 11',
            ],
        ];
    }
}
