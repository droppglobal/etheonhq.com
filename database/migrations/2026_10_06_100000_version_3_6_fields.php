<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Phones running the mobile app, for push notifications through Firebase Cloud Messaging
        Schema::create('mobile_devices', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('parent_id')->default(0);
            $table->string('token_hash', 64)->unique();
            $table->text('fcm_token');
            $table->string('platform', 20)->nullable(); // android, ios
            $table->string('app_version', 20)->nullable();
            $table->string('device_name')->nullable();
            $table->dateTime('last_seen_at')->nullable();
            $table->timestamps();
            $table->index('user_id');
        });

        // In-app notification inbox (everything sent as a push is also kept here)
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('title');
            $table->string('body', 500)->nullable();
            $table->string('screen', 30)->default('home');
            $table->string('url', 500)->nullable();
            $table->dateTime('read_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'read_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('app_notifications');
        Schema::dropIfExists('mobile_devices');
    }
};
