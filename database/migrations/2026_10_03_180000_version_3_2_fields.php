<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // AI triage of maintenance requests
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->string('ai_priority')->nullable()->after('status');
            $table->text('ai_summary')->nullable()->after('ai_priority');
            $table->string('source')->default('web')->after('ai_summary');
        });
    }

    public function down()
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropColumn(['ai_priority', 'ai_summary', 'source']);
        });
    }
};
