<?php

namespace Database\Migrations;

use Illuminate\Database\Capsule\Manager as Capsule;

class CreateOrdersTable
{
    public function up()
    {
        Capsule::schema()->create('orders', function ($table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->enum('order_status', ['Pending', 'Paid', 'Cancelled'])->default('Pending');
            $table->string('stripe_payment_id', 255)->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
        });
        echo "Created: orders table\n";
    }

    public function down()
    {
        Capsule::schema()->dropIfExists('orders');
    }
}