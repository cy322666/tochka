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
        Schema::create('ord_persons', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('uuid')->nullable();
            $table->dateTime('create_date')->nullable();
            $table->string('name')->nullable();
            $table->json('roles')->nullable();
            $table->json('juridical_details')->nullable();
            $table->string('type')->nullable();
            $table->string('phone')->nullable();
            $table->string('inn')->nullable();
            $table->string('rs_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ord_persons');
    }
};
