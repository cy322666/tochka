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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->integer('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('name')->nullable();
            $table->integer('order_id')->nullable();
            $table->string('positions')->nullable();
            $table->float('left_cost_money')->nullable();
            $table->float('cost_money')->nullable();
            $table->float('payed_money')->nullable();
            $table->string('payment_link', 500)->nullable();
            $table->string('status_order')->nullable();
            $table->boolean('status')->default(false);

            $table->integer('lead_id')->nullable();
            $table->integer('contact_id')->nullable();
            $table->integer('pipeline_id')->nullable();
            $table->integer('status_id')->nullable();

            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('utm_campaign')->nullable();

            $table->json('body')->nullable();

            $table->string('error', 500)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
