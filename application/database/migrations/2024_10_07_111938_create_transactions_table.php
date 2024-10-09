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
        Schema::create('sheets_transactions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->integer('link_id')->nullable();
            $table->integer('lead_id')->nullable();
            $table->string('url')->nullable();
            $table->string('name')->nullable();
            $table->boolean('check_1')->default(false);
            $table->integer('count_1')->nullable();
            $table->boolean('check_2')->default(false);
            $table->integer('count_2')->nullable();
            $table->boolean('status')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sheets_transactions');
    }
};
