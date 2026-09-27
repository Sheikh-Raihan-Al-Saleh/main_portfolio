<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_sections', function (Blueprint $table) {
            // A section now belongs to either a project's landing page or the
            // company's home page, so the project-scoped key becomes optional.
            $table->unsignedBigInteger('landing_page_id')->nullable()->change();
            $table->foreignId('company_id')
                ->nullable()
                ->constrained('companies')
                ->cascadeOnDelete();
        });

        Schema::table('landing_sections', function (Blueprint $table) {
            $table->index(['company_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::table('landing_sections', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
            $table->dropIndex(['landing_page_id', 'sort_order']);
        });

        Schema::table('landing_sections', function (Blueprint $table) {
            $table->unsignedBigInteger('landing_page_id')->nullable(false)->change();
        });
    }
};
