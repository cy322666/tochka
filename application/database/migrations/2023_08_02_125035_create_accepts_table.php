<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('msg_accepts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->integer('lead_id')->nullable();
            $table->dateTime('lead_created_at')->nullable();
            $table->dateTime('first_out')->nullable();
            $table->integer('time')->nullable();
            $table->integer('talk_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('msg_accepts');
    }
};
