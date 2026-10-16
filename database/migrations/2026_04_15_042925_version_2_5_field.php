<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agreements', function (Blueprint $table) {
            $table->text('landlord_signature')->nullable()->after('parent_id');
            $table->dateTime('landlord_signature_date')->nullable()->after('landlord_signature');
            $table->text('tenant_signature')->nullable()->after('landlord_signature_date');
            $table->dateTime('tenant_signature_date')->nullable()->after('tenant_signature');
            $table->integer('tenant_id')->default(0)->after('unit_id');
            $table->date('agreement_start_date')->nullable()->after('date');
            $table->date('agreement_end_date')->nullable()->after('date');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->integer('enabled_whatsapp')->default(0)->after('enabled_sms');
        });

        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->integer('tenant_id')->default(0)->after('unit_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->integer('is_recurring')->default(0)->after('end_date');
            $table->integer('recurring_date')->default(0)->after('is_recurring');
        });

        DB::statement("ALTER TABLE settings MODIFY value TEXT NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agreements', function (Blueprint $table) {
            $table->dropColumn('landlord_signature');
            $table->dropColumn('landlord_signature_date');
            $table->dropColumn('tenant_signature');
            $table->dropColumn('tenant_signature_date');
            $table->dropColumn('tenant_id');
            $table->dropColumn('agreement_start_date');
            $table->dropColumn('agreement_end_date');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('enabled_whatsapp');
        });

        DB::statement("ALTER TABLE settings MODIFY value VARCHAR(190) NOT NULL");

        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('is_recurring');
            $table->dropColumn('recurring_date');
        });
    }
};
