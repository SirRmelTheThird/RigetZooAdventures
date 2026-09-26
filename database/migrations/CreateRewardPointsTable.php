<?php

namespace Database\Migrations;

use Illuminate\Database\Capsule\Manager as Capsule;

class CreateRewardPointsTable
{
    public function up()
    {
        Capsule::schema()->create('reward_points', function ($table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id');
            $table->integer('points')->default(0);
            $table->foreignUuid('order_id')->nullable();
            $table->string('transaction_type', 50)->default('earned');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
        });
        echo "Created: reward_points table\n";
    }

    public function down()
    {
        Capsule::schema()->dropIfExists('reward_points');
    }
}
