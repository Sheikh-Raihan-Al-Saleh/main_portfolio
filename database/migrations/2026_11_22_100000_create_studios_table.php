<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The company (studio) behind the site. Its name and logo brand every page.
 * One row today; the unique slug keeps the table ready for more studios.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('studios', function (Blueprint $table) {
            $table->id();

            // Brand
            $table->string('name', 120);
            $table->string('short_name', 60)->nullable();
            $table->string('legal_name', 180)->nullable();
            $table->string('slug', 140)->unique();
            $table->string('logo_path')->nullable();
            $table->string('logo_dark_path')->nullable();
            $table->string('logo_mark_path')->nullable();

            // Story
            $table->string('headline', 150)->nullable();
            $table->string('tagline', 250)->nullable();
            $table->text('bio')->nullable();
            $table->text('mission')->nullable();
            $table->unsignedSmallInteger('founded_year')->nullable();
            $table->boolean('is_accepting_projects')->default(true);

            // Contact
            $table->string('location', 120)->nullable();
            $table->string('public_email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('website_url')->nullable();

            // Hero
            $table->string('hero_eyebrow', 80)->nullable();
            $table->string('hero_title')->nullable();
            $table->string('hero_statement')->nullable();
            $table->string('primary_cta_label', 60)->nullable();
            $table->string('primary_cta_url')->nullable();
            $table->string('secondary_cta_label', 60)->nullable();
            $table->string('secondary_cta_url')->nullable();

            // Flexible display copy. Never filtered on, so JSON is fine here.
            $table->json('footer')->nullable();
            $table->json('content')->nullable();

            // SEO
            $table->string('meta_title', 70)->nullable();
            $table->string('meta_description', 180)->nullable();
            $table->string('og_image_path')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('studios');
    }
};
