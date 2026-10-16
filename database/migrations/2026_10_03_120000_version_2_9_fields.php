<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Prospective tenants: website enquiries and manually added contacts
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->integer('property_id')->default(0);
            $table->integer('unit_id')->default(0);
            $table->string('source')->default('manual');
            $table->string('status')->default('new');
            $table->text('message')->nullable();
            $table->text('notes')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->dateTime('last_activity_at')->nullable();
            $table->integer('tenant_id')->default(0);
            $table->integer('parent_id')->default(0);
            $table->timestamps();
        });

        Schema::create('lead_viewings', function (Blueprint $table) {
            $table->id();
            $table->integer('lead_id')->default(0);
            $table->integer('property_id')->default(0);
            $table->integer('unit_id')->default(0);
            $table->date('viewing_date')->nullable();
            $table->string('viewing_time')->nullable();
            $table->string('status')->default('requested');
            $table->text('notes')->nullable();
            $table->integer('parent_id')->default(0);
            $table->timestamps();
        });

        Schema::create('rental_applications', function (Blueprint $table) {
            $table->id();
            $table->integer('lead_id')->default(0);
            $table->integer('property_id')->default(0);
            $table->integer('unit_id')->default(0);
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('current_address')->nullable();
            $table->string('employer')->nullable();
            $table->decimal('monthly_income', 15, 2)->default(0);
            $table->integer('family_member')->default(1);
            $table->date('move_in_date')->nullable();
            $table->string('id_document')->nullable();
            $table->string('income_document')->nullable();
            $table->text('message')->nullable();
            $table->string('status')->default('pending');
            $table->text('review_notes')->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->integer('tenant_id')->default(0);
            $table->integer('parent_id')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rental_applications');
        Schema::dropIfExists('lead_viewings');
        Schema::dropIfExists('leads');
    }
};
