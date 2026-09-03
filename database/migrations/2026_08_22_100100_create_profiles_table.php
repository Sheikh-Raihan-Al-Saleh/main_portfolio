<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Your Name');
            $table->string('headline')->nullable();
            $table->string('tagline')->nullable();
            $table->text('bio')->nullable();
            $table->string('location')->nullable();
            $table->string('public_email')->nullable();
            $table->string('phone')->nullable();
            $table->string('avatar_path')->nullable();
            $table->string('resume_path')->nullable();
            $table->string('og_image_path')->nullable();
            $table->boolean('available_for_work')->default(true);
            $table->json('socials')->nullable();
            $table->json('roles')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
