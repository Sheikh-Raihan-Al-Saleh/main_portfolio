<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_landing_pages', function (Blueprint $table) {
            // Add new column for multiple hero images (JSON array)
            $table->json('hero_media_paths')->nullable()->after('hero_media_path');
        });

        // Migrate existing data from hero_media_path to hero_media_paths
        if (Schema::hasTable('project_landing_pages')) {
            DB::table('project_landing_pages')
                ->whereNotNull('hero_media_path')
                ->update([
                    'hero_media_paths' => DB::raw('JSON_ARRAY(hero_media_path)'),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('project_landing_pages', function (Blueprint $table) {
            $table->dropColumn('hero_media_paths');
        });
    }
};
