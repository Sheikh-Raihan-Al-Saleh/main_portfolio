<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The founder's work history. A null end_date means the role is current.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('founder_id')->constrained()->cascadeOnDelete();
            $table->string('company', 150);
            $table->string('role', 150);
            $table->string('employment_type', 32)->nullable();
            $table->string('location', 120)->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('description')->nullable();
            $table->string('company_url')->nullable();
            $table->string('logo_path')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['founder_id', 'sort_order']);
            $table->index(['founder_id', 'start_date']);
        });

        Schema::create('experience_highlights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('experience_id')->constrained()->cascadeOnDelete();
            $table->string('body', 300);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['experience_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experience_highlights');
        Schema::dropIfExists('experiences');
    }
};
