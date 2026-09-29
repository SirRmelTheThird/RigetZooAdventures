<?php

namespace Database\Migrations;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

class CreateProcessedWebhookEventsTable
{
    public function up()
    {
        Capsule::schema()->create('processed_webhook_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('event_id', 255)->unique();
            $table->timestamps();
        });

        echo "Created: processed_webhook_events table\n";
    }

    public function down()
    {
        Capsule::schema()->dropIfExists('processed_webhook_events');
    }
}
