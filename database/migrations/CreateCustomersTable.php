<?php

namespace Database\Migrations;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

class CreateCustomersTable
{
    public function up()
    {
        Capsule::schema()->create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('username', 50)->unique();
            $table->string('password', 255);
            $table->string('email', 100)->unique();
            $table->timestamps();
        });
        echo "Created: customers table\n";
    }

    public function down()
    {
        Capsule::schema()->dropIfExists('customers');
    }
}
