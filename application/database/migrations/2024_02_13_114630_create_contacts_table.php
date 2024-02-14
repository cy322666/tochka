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
        Schema::create('excel_filter_contacts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->integer('contact_id')->nullable();
            $table->integer('lead_id')->nullable();
            $table->boolean('is_success')->nullable();
            $table->integer('lead_id_success')->nullable();
            $table->string('phone')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('excel_filter_contacts');
    }
};
