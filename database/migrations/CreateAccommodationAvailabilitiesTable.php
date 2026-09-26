<?php

namespace Database\Migrations;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

class CreateAccommodationAvailabilitiesTable
{
    public function up()
    {
        Capsule::schema()->create('accommodation_availabilities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('accommodation_id')->constrained('accommodations')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('reason', 100)->nullable()->comment('e.g., maintenance, illness, season');
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->index(['accommodation_id', 'start_date', 'end_date'], 'avail_idx');
        });
        echo "Created: accommodation_availabilities table\n";
    }

    public function down()
    {
        Capsule::schema()->dropIfExists('accommodation_availabilities');
    }
}