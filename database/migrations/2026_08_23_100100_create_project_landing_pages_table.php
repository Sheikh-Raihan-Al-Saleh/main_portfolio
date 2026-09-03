<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_landing_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('eyebrow')->nullable();
            $table->string('headline')->nullable();
            $table->text('subheadline')->nullable();
            $table->string('hero_media_path')->nullable();
            $table->string('hero_video_url')->nullable();
            $table->string('primary_cta_label')->nullable();
            $table->string('primary_cta_url')->nullable();
            $table->string('secondary_cta_label')->nullable();
            $table->string('secondary_cta_url')->nullable();
            // Per-page gradient identity, so one component renders many looks.
            $table->string('accent_from', 32)->nullable();
            $table->string('accent_to', 32)->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->string('og_image_path')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_landing_pages');
    }
};
