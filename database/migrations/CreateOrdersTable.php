<?php

namespace Database\Migrations;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

class CreateOrdersTable
{
    public function up()
    {
        Capsule::schema()->create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->integer('order_number');
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->enum('order_status', ['Pending', 'Paid', 'Cancelled', 'Failed'])->default('Pending');
            $table->string('stripe_payment_id', 255)->nullable()->unique();
            $table->timestamps();

            $table->index(['customer_id', 'created_at']);
            $table->unique(['customer_id', 'order_number']);
        });
        echo "Created: orders table\n";
    }

    public function down()
    {
        Capsule::schema()->dropIfExists('orders');
    }
}
