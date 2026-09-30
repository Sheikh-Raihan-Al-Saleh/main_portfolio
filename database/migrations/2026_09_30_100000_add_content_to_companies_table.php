<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Copy for the studio's fixed story sections (capabilities,
            // engineering pipeline, value grid, closing CTA). Mirrors the
            // `content` column on profiles, so the same editor pattern serves
            // both records. Null falls back to hardcoded defaults.
            $table->json('content')->nullable()->after('footer');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('content');
        });
    }
};
