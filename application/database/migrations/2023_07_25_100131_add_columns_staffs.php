<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('amocrm_staffs', function (Blueprint $table) {
            $table->float('avg_out');
            $table->integer('count_out');
            $table->integer('count_in');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('amocrm_staffs', function (Blueprint $table) {
            $table->dropColumn('avg_out');
            $table->dropColumn('count_out');
            $table->dropColumn('count_in');
        });
    }
};
