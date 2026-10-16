<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('property_units', function (Blueprint $table) {
            $table->text('is_occupied')->nullable()->after('property_id');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->integer('exit_amount')->default(0)->after('lease_end_date');
            $table->integer('extra_charge')->default(0)->after('exit_amount');
            $table->date('exit_date')->nullable()->after('extra_charge');
            $table->text('reason')->nullable()->after('exit_date');
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->text('amenities_id')->nullable()->after('type');
            $table->text('advantage_id')->nullable()->after('amenities_id');
            $table->string('listing_type')->nullable()->after('advantage_id');
            $table->integer('price')->default(0)->after('listing_type');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('code')->nullable()->after('is_active');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->text('sms_message')->nullable();
            $table->integer('enabled_sms')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('property_units', function (Blueprint $table) {
            $table->dropColumn('is_occupied');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['exit_amount', 'extra_charge', 'exit_date', 'reason']);
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['amenities_id', 'advantage_id', 'listing_type', 'price']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('code');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('sms_message');
            $table->dropColumn('enabled_sms');
        });
    }
};
