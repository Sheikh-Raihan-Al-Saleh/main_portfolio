<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Schema fixes from the QA pass:
 *
 * - projects: every public listing filters on is_published + context and
 *   sorts by sort_order, which neither existing index covered.
 * - landing_sections: a section belongs to a project landing page OR the
 *   company home page, never both and never neither. Enforced with a CHECK
 *   constraint where the database supports adding one (MySQL 8.0.16+).
 * - companies.founded_year: a year, not free text. Anything non-numeric is
 *   cleared before the type change so the conversion cannot fail.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->index(['is_published', 'context', 'sort_order'], 'projects_public_listing_index');
        });

        if ($this->supportsCheckConstraints()) {
            DB::statement(
                'ALTER TABLE landing_sections ADD CONSTRAINT landing_sections_single_owner '
                .'CHECK ((landing_page_id IS NULL) <> (company_id IS NULL))'
            );
        }

        DB::table('companies')
            ->whereNotNull('founded_year')
            ->get(['id', 'founded_year'])
            ->each(function (object $company): void {
                if (! preg_match('/^\d{4}$/', trim((string) $company->founded_year))) {
                    DB::table('companies')->where('id', $company->id)->update(['founded_year' => null]);
                }
            });

        Schema::table('companies', function (Blueprint $table) {
            $table->unsignedSmallInteger('founded_year')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('founded_year', 20)->nullable()->change();
        });

        if ($this->supportsCheckConstraints()) {
            DB::statement('ALTER TABLE landing_sections DROP CHECK landing_sections_single_owner');
        }

        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex('projects_public_listing_index');
        });
    }

    private function supportsCheckConstraints(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
    }
};
