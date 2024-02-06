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
        Schema::create('filter_contecsts', function (Blueprint $table) {

            $table->id();
            $table->timestamps();
            $table->integer('list_id');
            $table->integer('client_id');
            $table->integer('contact_id')->nullable();
            $table->integer('lead_id')->nullable();
            $table->integer('status')->default(0);
            $table->integer('status_id')->nullable();
            $table->integer('pipeline_id')->nullable();
            $table->boolean('in_sales')->default(false);

            $table->index('list_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filter_contecsts');
    }
};
