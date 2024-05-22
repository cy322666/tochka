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
        Schema::create('docs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->uuid()->nullable();
            $table->integer('doc_id')->nullable();
            $table->string('name', 100)->nullable();
            $table->string('path')->nullable();
            $table->json('metadata')->nullable();
            $table->string('type', 20)->nullable();
            $table->string('href')->nullable();
            $table->dateTime('created_at_doc')->nullable();
            $table->dateTime('request_at')->nullable();
            $table->integer('lead_id')->nullable();
            $table->integer('contact_id')->nullable();
            $table->integer('company_id')->nullable();
            $table->integer('status')->default(0);

            $table->unique('doc_id');
            $table->index('lead_id');
            $table->index('status');
            $table->index('created_at_doc');
            $table->index('request_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('docs');
    }
};
