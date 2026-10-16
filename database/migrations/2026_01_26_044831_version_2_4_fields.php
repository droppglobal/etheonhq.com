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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->integer('enabled_openai')->default(0)->after('enabled_logged_history');
            $table->integer('enabled_n8n')->default(0)->after('enabled_openai');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->integer('tenant')->default(0)->after('property_id');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('enabled_openai');
            $table->dropColumn('enabled_n8n');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('tenant');
        });
    }
};
