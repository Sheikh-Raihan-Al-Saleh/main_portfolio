<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('headline')->nullable();
            $table->string('tagline')->nullable();
            $table->text('bio')->nullable();
            $table->text('mission')->nullable();
            $table->string('location')->nullable();
            $table->string('public_email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('founded_year', 20)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('og_image_path')->nullable();
            $table->string('hero_eyebrow')->nullable();
            $table->string('hero_title')->nullable();
            $table->string('hero_statement')->nullable();
            $table->string('primary_cta_label')->nullable();
            $table->string('primary_cta_url')->nullable();
            $table->string('secondary_cta_label')->nullable();
            $table->string('secondary_cta_url')->nullable();
            $table->string('status_text')->nullable();
            $table->boolean('accepting_projects')->default(true);
            $table->json('socials')->nullable();
            $table->json('footer')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
