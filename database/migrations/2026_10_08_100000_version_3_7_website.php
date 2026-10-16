<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// v3.7 owner website: section order, translations, new section types, owner pages
return new class extends Migration
{
    public function up()
    {
        Schema::table('front_home_pages', function (Blueprint $table) {
            if (!Schema::hasColumn('front_home_pages', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('enabled');
            }
            if (!Schema::hasColumn('front_home_pages', 'translations')) {
                $table->mediumText('translations')->nullable()->after('content_value');
            }
        });
        // repeatable boxes can make the content longer than TEXT allows
        DB::statement('ALTER TABLE front_home_pages MODIFY content_value MEDIUMTEXT NULL');

        if (!Schema::hasTable('website_pages')) {
            Schema::create('website_pages', function (Blueprint $table) {
                $table->id();
                $table->integer('parent_id')->default(0)->index();
                $table->string('title');
                $table->string('slug');
                $table->mediumText('content')->nullable();
                $table->mediumText('translations')->nullable();
                $table->boolean('show_in_menu')->default(1);
                $table->boolean('is_active')->default(1);
                $table->integer('sort_order')->default(0);
                $table->string('meta_title')->nullable();
                $table->string('meta_description', 300)->nullable();
                $table->timestamps();
                $table->unique(['parent_id', 'slug']);
            });
        }

        // every owner: new section types (switched off), old fixed boxes turned into lists, an order for each section
        foreach (DB::table('users')->where('type', 'owner')->pluck('id') as $ownerId) {
            \App\Support\WebsiteSections::ensure($ownerId);
        }
    }

    public function down()
    {
        Schema::dropIfExists('website_pages');
        $new = collect(\App\Support\WebsiteSections::definitions())->filter(fn($d) => !empty($d['new']))->keys();
        DB::table('front_home_pages')->whereIn('section', $new)->delete();
        Schema::table('front_home_pages', function (Blueprint $table) {
            $table->dropColumn(['sort_order', 'translations']);
        });
    }
};
