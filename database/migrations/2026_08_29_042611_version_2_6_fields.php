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
            $table->tinyInteger('free_trial')->default(0)->after('enabled_openai');
            $table->integer('free_trial_days')->default(0)->after('free_trial');
        });

        Schema::table('package_transactions', function (Blueprint $table) {
            $table->integer('free_trial')->default(0);
            $table->string('ip')->nullable();
            $table->string('email')->nullable();
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
            $table->dropColumn('free_trial');
            $table->dropColumn('free_trial_days');
        });
        Schema::table('package_transactions', function (Blueprint $table) {
            $table->dropColumn('free_trial')->default(0);
            $table->dropColumn('ip')->nullable();
            $table->dropColumn('email')->nullable()->after('free_trial');
        });
    }
};
