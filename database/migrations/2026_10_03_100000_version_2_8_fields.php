<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Auto late fee: an invoice gets at most one automatic late fee
        Schema::table('invoices', function (Blueprint $table) {
            $table->tinyInteger('late_fee_applied')->default(0)->after('recurring_date');
        });

        // Document vault: name, category and expiry tracking
        Schema::table('tenant_documents', function (Blueprint $table) {
            $table->string('title')->nullable()->after('document');
            $table->string('document_type')->nullable()->after('title');
            $table->date('expiry_date')->nullable()->after('document_type');
            $table->tinyInteger('expiry_reminder_sent')->default(0)->after('expiry_date');
        });

        // Agreement signing audit trail
        Schema::table('agreements', function (Blueprint $table) {
            $table->string('tenant_signature_ip')->nullable()->after('tenant_signature_date');
            $table->text('tenant_signature_agent')->nullable()->after('tenant_signature_ip');
            $table->dateTime('signed_copy_sent_at')->nullable()->after('tenant_signature_agent');
        });

        // Security deposit ledger
        Schema::create('deposit_transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id')->default(0);
            $table->integer('property_id')->default(0);
            $table->integer('unit_id')->default(0);
            $table->string('type'); // received, deduction, refund
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('date')->nullable();
            $table->text('notes')->nullable();
            $table->integer('parent_id')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('late_fee_applied');
        });

        Schema::table('tenant_documents', function (Blueprint $table) {
            $table->dropColumn(['title', 'document_type', 'expiry_date', 'expiry_reminder_sent']);
        });

        Schema::table('agreements', function (Blueprint $table) {
            $table->dropColumn(['tenant_signature_ip', 'tenant_signature_agent', 'signed_copy_sent_at']);
        });

        Schema::dropIfExists('deposit_transactions');
    }
};
