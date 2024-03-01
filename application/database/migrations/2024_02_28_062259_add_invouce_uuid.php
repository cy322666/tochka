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
        Schema::table('ord_transactions', function (Blueprint $table) {

            $table->string('invoice_uuid', 30)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ord_contracts', function (Blueprint $table) {

            $table->dropIndex('invoice_uuid');
        });
    }
};
