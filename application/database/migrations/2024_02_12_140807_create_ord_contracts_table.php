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
        Schema::create('ord_contracts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('uuid')->nullable();
            $table->dateTime('create_date')->nullable();
            $table->string('type')->nullable();
            $table->string('client_external_id')->nullable();
            $table->string('contractor_external_id')->nullable();
            $table->string('subject_type')->nullable();
            $table->date('date')->nullable();
            $table->string('serial')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ord_contracts');
    }
};
