<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Move-in / move-out / routine inspections with a room-by-room checklist
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id')->default(0);
            $table->integer('property_id')->default(0);
            $table->integer('unit_id')->default(0);
            $table->string('type')->default('move_in');
            $table->date('inspection_date')->nullable();
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->string('tenant_signed_name')->nullable();
            $table->dateTime('tenant_signed_at')->nullable();
            $table->string('tenant_signed_ip')->nullable();
            $table->integer('parent_id')->default(0);
            $table->timestamps();
        });

        Schema::create('inspection_items', function (Blueprint $table) {
            $table->id();
            $table->integer('inspection_id')->default(0);
            $table->string('area');
            $table->string('item');
            $table->string('condition')->default('good');
            $table->text('notes')->nullable();
            $table->string('photo')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Work orders: completion photo, time spent, tenant rating, outside vendor, source schedule
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->string('after_photo')->nullable()->after('invoice');
            $table->decimal('labor_hours', 8, 2)->default(0)->after('after_photo');
            $table->tinyInteger('tenant_rating')->default(0)->after('labor_hours');
            $table->text('tenant_feedback')->nullable()->after('tenant_rating');
            $table->integer('vendor_id')->default(0)->after('maintainer_id');
            $table->integer('schedule_id')->default(0)->after('vendor_id');
        });

        // Utility billing from meter readings
        Schema::create('meter_readings', function (Blueprint $table) {
            $table->id();
            $table->integer('property_id')->default(0);
            $table->integer('unit_id')->default(0);
            $table->string('utility_type');
            $table->date('reading_date')->nullable();
            $table->decimal('previous_reading', 15, 2)->default(0);
            $table->decimal('current_reading', 15, 2)->default(0);
            $table->decimal('rate', 15, 4)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->integer('invoice_id')->default(0);
            $table->text('notes')->nullable();
            $table->integer('parent_id')->default(0);
            $table->timestamps();
        });

        // Preventive maintenance: repeating tasks that create maintenance requests
        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('property_id')->default(0);
            $table->integer('unit_id')->default(0);
            $table->integer('issue_type')->default(0);
            $table->integer('maintainer_id')->default(0);
            $table->integer('vendor_id')->default(0);
            $table->string('frequency')->default('monthly');
            $table->date('next_date')->nullable();
            $table->date('last_run_date')->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->text('notes')->nullable();
            $table->integer('parent_id')->default(0);
            $table->timestamps();
        });

        // Outside contractors
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('service')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->integer('parent_id')->default(0);
            $table->timestamps();
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->integer('vendor_id')->default(0)->after('unit_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('inspections');
        Schema::dropIfExists('inspection_items');
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropColumn(['after_photo', 'labor_hours', 'tenant_rating', 'tenant_feedback', 'vendor_id', 'schedule_id']);
        });
        Schema::dropIfExists('meter_readings');
        Schema::dropIfExists('maintenance_schedules');
        Schema::dropIfExists('vendors');
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('vendor_id');
        });
    }
};
