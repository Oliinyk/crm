<?php

use App\Models\Layer;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Workspace::class);
            $table->foreignIdFor(TicketType::class);
            $table->foreignIdFor(Project::class);
            $table->foreignIdFor(User::class, 'author_id');
            $table->foreignIdFor(Ticket::class, 'parent_ticket_id')->nullable();
            $table->foreignIdFor(User::class, 'assignee_id')->nullable();
            $table->foreignIdFor(Layer::class)->nullable();
            $table->string('title');
            $table->enum('status', [
                Ticket::STATUS_OPEN,
                Ticket::STATUS_IN_PROGRESS,
                Ticket::STATUS_RESOLVED,
            ])->nullable();
            $table->enum('priority', [
                Ticket::PRIORITY_LOW,
                Ticket::PRIORITY_MEDIUM,
                Ticket::PRIORITY_HIGH,
            ])->nullable();
            $table->unsignedTinyInteger('progress')->nullable();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->unsignedInteger('time_estimate')->nullable();
            $table->unsignedInteger('time_spent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tickets');
    }
};
