<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The person behind the studio: the personal portfolio on /founder.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('founders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('studio_id')->nullable()->constrained()->nullOnDelete();

            // Identity
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->string('headline', 150)->nullable();
            $table->string('tagline', 250)->nullable();
            $table->text('bio')->nullable();
            $table->text('founder_message')->nullable();
            $table->json('roles')->nullable();
            $table->boolean('is_available_for_work')->default(true);

            // Contact
            $table->string('location', 120)->nullable();
            $table->string('public_email')->nullable();
            $table->string('phone', 40)->nullable();

            // Media
            $table->string('avatar_path')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('resume_path')->nullable();

            // Hero
            $table->string('hero_title')->nullable();
            $table->string('hero_statement')->nullable();
            $table->string('primary_cta_label', 60)->nullable();
            $table->string('primary_cta_url')->nullable();
            $table->string('secondary_cta_label', 60)->nullable();
            $table->string('secondary_cta_url')->nullable();

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
        Schema::dropIfExists('founders');
    }
};
