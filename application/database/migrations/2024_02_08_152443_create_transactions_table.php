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
        Schema::create('ord_transactions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('lead_id');
            $table->integer('contact_id');
            $table->integer('company_id');
            $table->boolean('status')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ord_transactions');
    }
};
