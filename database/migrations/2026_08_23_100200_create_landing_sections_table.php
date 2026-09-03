<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landing_page_id')
                ->constrained('project_landing_pages')
                ->cascadeOnDelete();
            $table->string('type', 40);
            $table->string('eyebrow')->nullable();
            $table->string('heading')->nullable();
            $table->text('subheading')->nullable();
            $table->text('body')->nullable();
            // Per-type payload; shape is owned by LandingSectionType::rules().
            $table->json('data')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->index(['landing_page_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_sections');
    }
};
