<?php

namespace Tests\Unit;

use App\Enums\PermissionsTestEnum;
use App\Models\Client;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function client_must_have_filter_scope()
    {
        Client::factory()->count(10)->create();

        $client = Client::factory()->create([
            'name' => '123123kek',
        ]);

        $result = Client::filter(['search' => '123123kek'])->get();

        $this->assertCount(1, $result);
        $this->assertSame($client->id, $result->first()->id);
    }

    /**
     * @test
     */
    public function client_must_belongs_to_the_workspace()
    {
        $client = Client::factory()->create();

        $this->assertInstanceOf(Workspace::class, $client->workspace);
    }
}
