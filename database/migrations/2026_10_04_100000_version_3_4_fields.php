<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Browser / phone push notifications (one row per device)
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('parent_id')->default(0);
            $table->string('endpoint_hash', 64)->unique();
            $table->text('endpoint');
            $table->string('public_key');
            $table->string('auth_token');
            $table->string('user_agent')->nullable();
            $table->timestamps();
            $table->index('user_id');
        });

        // Tenant assistant conversations (web chat and WhatsApp), visible to the owner
        Schema::create('assistant_messages', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id');
            $table->integer('tenant_id');
            $table->string('role', 20); // user, assistant, staff
            $table->text('content');
            $table->string('channel', 20)->default('web'); // web, whatsapp
            $table->integer('staff_id')->nullable();
            $table->tinyInteger('needs_reply')->default(0);
            $table->timestamps();
            $table->index(['parent_id', 'tenant_id']);
        });

        // Short public pay link for each invoice
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('pay_token', 40)->nullable()->unique();
            $table->string('autopay_pending_id')->nullable(); // Razorpay payment still being processed
        });

        // Razorpay autopay (saved card / UPI mandate token)
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('autopay_gateway', 20)->nullable();
            $table->string('razorpay_customer_id')->nullable();
            $table->string('razorpay_token_id')->nullable();
            $table->string('razorpay_method')->nullable();
        });
        \DB::table('tenants')->whereNotNull('stripe_payment_method_id')->update(['autopay_gateway' => 'stripe']);

        // {payment_link} short code for invoice and reminder templates
        foreach (\DB::table('notifications')->whereIn('module', ['invoice_create', 'payment_reminder'])->get() as $row) {
            $codes = json_decode($row->short_code, true) ?: [];
            if (!in_array('{payment_link}', $codes)) {
                $codes[] = '{payment_link}';
                \DB::table('notifications')->where('id', $row->id)->update(['short_code' => json_encode($codes)]);
            }
        }

        // The old AI Content Generator was replaced by "Write with AI" in v3.2
        Schema::dropIfExists('ai_templates');
    }

    public function down()
    {
        Schema::dropIfExists('push_subscriptions');
        Schema::dropIfExists('assistant_messages');
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['pay_token', 'autopay_pending_id']);
        });
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['autopay_gateway', 'razorpay_customer_id', 'razorpay_token_id', 'razorpay_method']);
        });
    }
};
