<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Denormalised so listing queries can flag "has a case study"
            // without joining the landing page table on every row.
            $table->boolean('has_landing_page')->default(false)->after('is_published');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('has_landing_page');
        });
    }
};
