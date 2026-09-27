<?php

use App\Enums\ProjectContext;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Existing rows are the founder's own portfolio, so they default to
            // the personal context rather than the company site.
            $table->string('context', 20)
                ->default(ProjectContext::Personal->value)
                ->after('slug')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('context');
        });
    }
};
