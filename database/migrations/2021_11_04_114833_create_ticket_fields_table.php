<?php

use App\Models\TicketField;
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
        Schema::create('ticket_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Workspace::class);
            $table->enum('type', TicketField::ticketTypes());
            $table->string('name')->nullable();
            $table->unsignedTinyInteger('order');
            $table->morphs('ticket_field');

            $table->text('value')->nullable();

            $table->date('date_value')->nullable();
            $table->time('time_value')->nullable();
            $table->string('string_value')->nullable();
            $table->text('text_value')->nullable();
            $table->integer('int_value')->nullable();
            $table->decimal('decimal_value')->nullable();
            $table->boolean('boolean_value')->nullable();

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
        Schema::dropIfExists('ticket_fields');
    }
};
