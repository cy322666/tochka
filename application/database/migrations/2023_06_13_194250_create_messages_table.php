<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('msg_messages', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('message_id')->unique();
            $table->integer('responsible_user_id');
            $table->dateTime('msg_at');
            $table->integer('talk_id');
            $table->string('element_type');
            $table->integer('element_id');
            $table->integer('entity_id');
            $table->enum('type', ['out', 'in']);
            $table->string('origin');

            $table->index('message_id');
            $table->index('msg_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
