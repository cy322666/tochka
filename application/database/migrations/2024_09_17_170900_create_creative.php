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
        Schema::create('ord_creative', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('uuid')->nullable();
            $table->string('name')->nullable();
            $table->json('media')->nullable();
            $table->string('erid')->nullable();
            $table->string('contract_external_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ord_creative');
    }
};
