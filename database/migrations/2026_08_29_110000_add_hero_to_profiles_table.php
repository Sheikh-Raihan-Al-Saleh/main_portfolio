<?php

use App\Models\Profile;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('hero_title')->nullable()->after('name');
            $table->string('hero_statement')->nullable()->after('hero_title');
        });

        DB::table('profiles')->update(Profile::heroDefaults());
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['hero_title', 'hero_statement']);
        });
    }
};
