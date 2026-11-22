<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A project's marketing case-study page (/p/{slug}) and its hero media slots.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_landing_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('eyebrow', 80)->nullable();
            $table->string('headline', 180)->nullable();
            $table->string('subheadline', 600)->nullable();
            $table->string('hero_video_url', 2048)->nullable();
            $table->string('primary_cta_label', 80)->nullable();
            $table->string('primary_cta_url', 2048)->nullable();
            $table->string('secondary_cta_label', 80)->nullable();
            $table->string('secondary_cta_url', 2048)->nullable();
            $table->char('accent_from', 7)->nullable();
            $table->char('accent_to', 7)->nullable();
            $table->string('seo_title', 180)->nullable();
            $table->string('seo_description', 300)->nullable();
            $table->string('og_image_path')->nullable();
            $table->boolean('is_published')->default(false)->index();
            $table->timestamps();
        });

        // Positional hero screenshots: 0 = desktop, 1 = tablet, 2 = phone.
        Schema::create('landing_page_hero_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landing_page_id')->constrained('project_landing_pages')->cascadeOnDelete();
            $table->unsignedTinyInteger('slot');
            $table->string('path');
            $table->timestamps();

            $table->unique(['landing_page_id', 'slot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_page_hero_media');
        Schema::dropIfExists('project_landing_pages');
    }
};
