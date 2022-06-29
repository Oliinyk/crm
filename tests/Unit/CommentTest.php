<?php

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function comment_belongs_to_workspace()
    {
        $comment = Comment::factory()->create();

        $this->assertInstanceOf(Workspace::class, $comment->workspace);
    }

    /**
     * @test
     */
    public function comment_belongs_to_the_ticket()
    {
        $comment = Comment::factory()->create();

        $this->assertInstanceOf(Ticket::class, $comment->ticket);
    }

    /**
     * @test
     */
    public function it_must_belongs_to_the_author()
    {
        $comment = Comment::factory()->create();

        $this->assertInstanceOf(User::class, $comment->author);
    }
}
