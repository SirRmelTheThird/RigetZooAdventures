<?php

namespace Database\Migrations;

use Illuminate\Database\Capsule\Manager as Capsule;

class CreateAccommodationsTable
{
    public function up()
    {
        Capsule::schema()->create('accommodations', function ($table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description');
            $table->decimal('price_per_night', 10, 2);
            $table->integer('max_guests');
            $table->integer('available_rooms')->default(10);
            $table->string('location', 100)->nullable();
            $table->string('image_url', 255)->nullable();
            $table->timestamps();
        });
        echo "Created: accommodations table\n";
    }

    public function down()
    {
        Capsule::schema()->dropIfExists('accommodations');
    }
}