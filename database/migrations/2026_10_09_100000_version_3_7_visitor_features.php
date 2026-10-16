<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// v3.7 owner website, visitor features: tenant reviews and new-listing email alerts
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('website_reviews')) {
            Schema::create('website_reviews', function (Blueprint $table) {
                $table->id();
                $table->integer('parent_id')->default(0)->index();
                $table->integer('user_id')->default(0)->index(); // the tenant's login
                $table->string('name');
                $table->string('role')->nullable();
                $table->tinyInteger('rating')->default(5);
                $table->text('review');
                $table->string('status')->default('pending'); // pending, approved, hidden
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('listing_alerts')) {
            Schema::create('listing_alerts', function (Blueprint $table) {
                $table->id();
                $table->integer('parent_id')->default(0)->index();
                $table->integer('lead_id')->default(0);
                $table->string('name')->nullable();
                $table->string('email');
                $table->text('filters')->nullable();
                $table->string('token', 64)->unique();
                $table->text('sent_ids')->nullable(); // properties already emailed
                $table->timestamp('last_sent_at')->nullable();
                $table->timestamp('unsubscribed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('website_reviews');
        Schema::dropIfExists('listing_alerts');
    }
};
