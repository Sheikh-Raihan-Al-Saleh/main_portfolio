<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Composable content blocks. The polymorphic owner is a project landing page
 * or the studio home page. Both share the same block types and editor.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_sections', function (Blueprint $table) {
            $table->id();
            $table->string('sectionable_type', 32);
            $table->unsignedBigInteger('sectionable_id');
            $table->string('type', 32);
            $table->string('eyebrow', 80)->nullable();
            $table->string('heading', 180)->nullable();
            $table->string('subheading', 400)->nullable();
            $table->text('body')->nullable();
            // Block-specific fields, validated per type by LandingSectionType::dataRules().
            $table->json('data')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->index(['sectionable_type', 'sectionable_id', 'is_visible', 'sort_order'], 'landing_sections_owner_listing_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_sections');
    }
};
