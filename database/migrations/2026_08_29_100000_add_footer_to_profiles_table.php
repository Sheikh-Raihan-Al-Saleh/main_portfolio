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
            $table->json('footer')->nullable()->after('roles');
        });

        DB::table('profiles')
            ->whereNull('footer')
            ->update([
                'footer' => json_encode(
                    Profile::footerDefaults(),
                    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                ),
            ]);
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('footer');
        });
    }
};
