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

            $table->string('erid')->nullable();
            $table->string('marker')->nullable();
            $table->string('parent_contract_external_id')->nullable();
            $table->string('contract_serial')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ord_transactions', function (Blueprint $table) {

            $table->dropColumn('erid');
            $table->dropColumn('marker');
            $table->dropColumn('parent_contract_external_id');
            $table->dropColumn('contract_serial');
        });
    }
};
