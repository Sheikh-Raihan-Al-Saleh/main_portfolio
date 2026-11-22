<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Portfolio work. The polymorphic owner is the studio (client and company
 * work) or the founder (personal work).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('owner_type', 32);
            $table->unsignedBigInteger('owner_id');
            $table->string('title', 180);
            $table->string('slug', 200)->unique();
            $table->string('summary', 500)->nullable();
            $table->longText('description')->nullable();
            $table->string('cover_image_path')->nullable();
            $table->string('role', 120)->nullable();
            $table->string('repo_url')->nullable();
            $table->string('live_url')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->date('started_at')->nullable();
            $table->date('completed_at')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();

            // "Published work of this owner, in display order": the main read path.
            $table->index(['owner_type', 'owner_id', 'is_published', 'sort_order'], 'projects_owner_listing_index');
            $table->index(['is_published', 'is_featured', 'sort_order'], 'projects_featured_listing_index');
        });

        Schema::create('project_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('alt', 180)->nullable();
            $table->string('caption', 300)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['project_id', 'sort_order']);
        });

        Schema::create('project_technology', function (Blueprint $table) {
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technology_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->primary(['project_id', 'technology_id']);
            // Reverse lookup for "projects using this technology" filters.
            $table->index('technology_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_technology');
        Schema::dropIfExists('project_images');
        Schema::dropIfExists('projects');
    }
};
