<?php

namespace Database\Migrations;

use Illuminate\Database\Capsule\Manager as Capsule;

class CreateTicketsTable
{
    public function up()
    {
        Capsule::schema()->create('tickets', function ($table) {
            $table->id();
            $table->string('type', 50);
            $table->string('category', 50);
            $table->decimal('price', 10, 2);
            $table->text('description')->nullable();
            $table->integer('available_quantity')->default(1000);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
        echo "Created: tickets table\n";
    }

    public function down()
    {
        Capsule::schema()->dropIfExists('tickets');
    }
}