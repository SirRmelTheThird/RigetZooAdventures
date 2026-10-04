<?php

namespace Database\Migrations;

use Illuminate\Database\Capsule\Manager as Capsule;

class AddStripeCustomerIdToCustomersTable
{
    public function up()
    {
        if (Capsule::schema()->hasColumn('customers', 'stripe_customer_id')) {
            echo "Skipping: customers.stripe_customer_id already exists\n";

            return;
        }

        Capsule::schema()->table('customers', function ($table) {
            $table->string('stripe_customer_id', 255)->nullable()->unique();
        });
        echo "Added: customers.stripe_customer_id\n";
    }

    public function down()
    {
        Capsule::schema()->table('customers', function ($table) {
            $table->dropUnique(['stripe_customer_id']);
            $table->dropColumn('stripe_customer_id');
        });
    }
}
