<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// v3.7 owner website, SEO & stats: custom domain as a plan feature, daily property page views
return new class extends Migration
{
    public function up()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('subscriptions', 'enabled_custom_domain')) {
                $table->integer('enabled_custom_domain')->default(0)->after('enabled_n8n');
            }
        });

        if (!Schema::hasTable('property_views')) {
            Schema::create('property_views', function (Blueprint $table) {
                $table->id();
                $table->integer('parent_id')->default(0);
                $table->integer('property_id')->default(0);
                $table->date('day');
                $table->integer('views')->default(0);
                $table->unique(['property_id', 'day']);
                $table->index(['parent_id', 'day']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('property_views');
        Schema::table('subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('subscriptions', 'enabled_custom_domain')) {
                $table->dropColumn('enabled_custom_domain');
            }
        });
    }
};
