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

            $table->string('person_uuid')->nullable();
            $table->string('contract_uuid')->nullable();
            $table->string('creative_uuid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ord_transactions', function (Blueprint $table) {

            $table->dropColumn('person_uuid');
            $table->dropColumn('contract_uuid');
            $table->dropColumn('creative_uuid');
        });
    }
};
