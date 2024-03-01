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
        Schema::create('ord_pads', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('uuid')->nullable();
            $table->dateTime('create_date')->nullable();
            $table->string('person_external_id')->nullable();
            $table->boolean('is_owner')->nullable();
            $table->string('type')->nullable();
            $table->string('name')->nullable();
            $table->string('url')->nullable();
        });

        Schema::table('ord_transactions', function (Blueprint $table) {

            $table->string('pad_uuid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ord_pads');

        Schema::table('ord_transactions', function (Blueprint $table) {

            $table->dropColumn('pad_uuid');
        });
    }
};
