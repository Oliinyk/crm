<?php

use App\Enums\TimeEntryTypeEnum;
use App\Models\Ticket;
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
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Workspace::class);
            $table->foreignIdFor(Ticket::class);
            $table->foreignIdFor(User::class, 'author_id');
            $table->enum('type', TimeEntryTypeEnum::values());
            $table->text('description')->nullable();
            $table->integer('time');
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
        Schema::dropIfExists('time_entries');
    }
};
