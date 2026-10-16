<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Credit notes (reduce what is owed) and refunds (money paid back) on invoices
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id');
            $table->integer('invoice_id');
            $table->integer('number');
            $table->string('type', 20); // credit, refund
            $table->decimal('amount', 15, 2);
            $table->date('date');
            $table->string('method')->nullable(); // how a refund was paid back
            $table->integer('payment_id')->nullable(); // negative invoice_payments row that records a refund
            $table->text('reason')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
            $table->index(['parent_id', 'invoice_id']);
        });

        // One bill split across several properties is saved as one expense per property
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('split_group', 40)->nullable();
        });

        // Maintenance: tenant's preferred visit time and the maintainer's "on the way"
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->date('preferred_date')->nullable();
            $table->string('preferred_slot', 20)->nullable(); // morning, afternoon, evening, any
            $table->dateTime('on_the_way_at')->nullable();
        });

        // Move-in checklist ticks (automatic items are worked out from existing records)
        Schema::create('tenant_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id');
            $table->integer('tenant_id');
            $table->string('item', 50);
            $table->dateTime('done_at')->nullable();
            $table->integer('done_by')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'item']);
        });

        // Rent increase notices, applied to the unit (and recurring invoices) on the effective date
        Schema::create('rent_increases', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id');
            $table->integer('tenant_id');
            $table->integer('unit_id');
            $table->decimal('old_rent', 15, 2);
            $table->decimal('new_rent', 15, 2);
            $table->date('effective_date');
            $table->text('notes')->nullable();
            $table->dateTime('notice_sent_at')->nullable();
            $table->dateTime('applied_at')->nullable();
            $table->string('status', 20)->default('scheduled'); // scheduled, applied, cancelled
            $table->integer('created_by')->nullable();
            $table->timestamps();
            $table->index(['parent_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('credit_notes');
        Schema::dropIfExists('tenant_checklist_items');
        Schema::dropIfExists('rent_increases');
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropColumn(['preferred_date', 'preferred_slot', 'on_the_way_at']);
        });
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('split_group');
        });
    }
};
