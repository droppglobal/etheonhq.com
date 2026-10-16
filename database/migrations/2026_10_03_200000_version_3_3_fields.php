<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Bookable shared spaces (gym, party hall, guest room...)
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->integer('property_id')->default(0);
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('open_time')->default('06:00');
            $table->string('close_time')->default('22:00');
            $table->integer('slot_minutes')->default(60);
            $table->integer('max_bookings_per_week')->default(0);
            $table->tinyInteger('requires_approval')->default(0);
            $table->tinyInteger('is_active')->default(1);
            $table->integer('parent_id')->default(0);
            $table->timestamps();
        });

        Schema::create('facility_bookings', function (Blueprint $table) {
            $table->id();
            $table->integer('facility_id')->default(0);
            $table->integer('tenant_id')->default(0);
            $table->date('booking_date');
            $table->string('start_time');
            $table->string('end_time');
            $table->string('status')->default('approved');
            $table->text('notes')->nullable();
            $table->integer('parent_id')->default(0);
            $table->timestamps();
        });

        // Visitor passes created by tenants and checked at the gate
        Schema::create('visitor_passes', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id')->default(0);
            $table->integer('property_id')->default(0);
            $table->integer('unit_id')->default(0);
            $table->string('visitor_name');
            $table->string('visitor_phone')->nullable();
            $table->string('purpose')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->date('visit_date');
            $table->string('code', 12);
            $table->string('status')->default('active');
            $table->dateTime('checked_in_at')->nullable();
            $table->dateTime('checked_out_at')->nullable();
            $table->integer('checked_in_by')->default(0);
            $table->integer('parent_id')->default(0);
            $table->timestamps();
            $table->index(['parent_id', 'code']);
        });

        // Parking
        Schema::create('parking_slots', function (Blueprint $table) {
            $table->id();
            $table->integer('property_id')->default(0);
            $table->string('code');
            $table->string('vehicle_type')->default('car');
            $table->decimal('monthly_fee', 15, 2)->default(0);
            $table->tinyInteger('is_active')->default(1);
            $table->integer('parent_id')->default(0);
            $table->timestamps();
        });

        Schema::create('parking_assignments', function (Blueprint $table) {
            $table->id();
            $table->integer('slot_id')->default(0);
            $table->integer('tenant_id')->default(0);
            $table->string('vehicle_number')->nullable();
            $table->string('vehicle_model')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('parent_id')->default(0);
            $table->timestamps();
        });

        // Polls
        Schema::create('polls', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('description')->nullable();
            $table->integer('property_id')->default(0);
            $table->date('closes_on')->nullable();
            $table->tinyInteger('show_results')->default(1);
            $table->tinyInteger('is_active')->default(1);
            $table->integer('parent_id')->default(0);
            $table->timestamps();
        });

        Schema::create('poll_options', function (Blueprint $table) {
            $table->id();
            $table->integer('poll_id')->default(0);
            $table->string('label');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('poll_votes', function (Blueprint $table) {
            $table->id();
            $table->integer('poll_id')->default(0);
            $table->integer('option_id')->default(0);
            $table->integer('tenant_id')->default(0);
            $table->timestamps();
            $table->unique(['poll_id', 'tenant_id']);
        });
    }

    public function down()
    {
        foreach (['poll_votes', 'poll_options', 'polls', 'parking_assignments', 'parking_slots', 'visitor_passes', 'facility_bookings', 'facilities'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
