<?php

namespace Tests\Unit;

use App\Models\Ticket;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeEntryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function time_entry_must_belongs_to_the_workspace()
    {
        $timeEntry = TimeEntry::factory()->create();

        $this->assertInstanceOf(Workspace::class, $timeEntry->workspace);
    }

    /**
     * @test
     */
    public function it_must_belongs_to_the_author()
    {
        $timeEntry = TimeEntry::factory()->create();

        $this->assertInstanceOf(User::class, $timeEntry->author);
    }

    /**
     * @test
     */
    public function it_must_belongs_to_the_ticket()
    {
        $timeEntry = TimeEntry::factory()->create();

        $this->assertInstanceOf(Ticket::class, $timeEntry->ticket);
    }
}
