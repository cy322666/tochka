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
        Schema::table('ord_contracts', function (Blueprint $table) {

            $table->unique('uuid');
            $table->string('parent_contract_external_id', 30)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ord_contracts', function (Blueprint $table) {

            $table->dropIndex('uuid');
            $table->dropColumn('parent_contract_external_id', 30);
        });
    }
};
