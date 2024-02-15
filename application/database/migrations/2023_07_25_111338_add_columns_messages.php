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
        Schema::table('msg_messages', function (Blueprint $table) {
            $table->time('msg_time_at')->nullable();
            $table->date('msg_date_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('msg_messages', function (Blueprint $table) {
            $table->dropColumn('msg_time_at');
            $table->dropColumn('msg_date_at');
        });
    }
};
