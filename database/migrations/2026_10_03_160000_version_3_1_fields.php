<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Tenant autopay with a saved Stripe card
        Schema::table('tenants', function (Blueprint $table) {
            $table->tinyInteger('autopay_enabled')->default(0);
            $table->string('stripe_customer_id')->nullable();
            $table->string('stripe_payment_method_id')->nullable();
            $table->string('card_brand')->nullable();
            $table->string('card_last4')->nullable();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dateTime('autopay_attempted_at')->nullable();
            $table->text('autopay_error')->nullable();
        });

        // Landlord (property owner) portal for agencies
        Schema::table('properties', function (Blueprint $table) {
            $table->integer('landlord_id')->default(0);
        });

        Schema::create('landlord_profiles', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->default(0);
            $table->decimal('management_fee_percent', 5, 2)->default(0);
            $table->text('bank_details')->nullable();
            $table->text('notes')->nullable();
            $table->integer('parent_id')->default(0);
            $table->timestamps();
        });

        Schema::create('landlord_payouts', function (Blueprint $table) {
            $table->id();
            $table->integer('landlord_id')->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('payout_date')->nullable();
            $table->string('method')->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->integer('parent_id')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['autopay_enabled', 'stripe_customer_id', 'stripe_payment_method_id', 'card_brand', 'card_last4']);
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['autopay_attempted_at', 'autopay_error']);
        });
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('landlord_id');
        });
        Schema::dropIfExists('landlord_profiles');
        Schema::dropIfExists('landlord_payouts');
    }
};
