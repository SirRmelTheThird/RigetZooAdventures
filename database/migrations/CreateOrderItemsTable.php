<?php

namespace Database\Migrations;

use Illuminate\Database\Capsule\Manager as Capsule;

class CreateOrderItemsTable
{
    public function up()
    {
        Capsule::schema()->create('order_items', function ($table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->enum('item_type', ['Ticket', 'Accommodation']);
            $table->unsignedBigInteger('ticket_id')->nullable();
            $table->unsignedBigInteger('accommodation_id')->nullable();
            $table->integer('quantity')->default(1);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('set null');
            $table->foreign('accommodation_id')->references('id')->on('accommodations')->onDelete('set null');
        });
        echo "Created: order_items table\n";
    }

    public function down()
    {
        Capsule::schema()->dropIfExists('order_items');
    }
}