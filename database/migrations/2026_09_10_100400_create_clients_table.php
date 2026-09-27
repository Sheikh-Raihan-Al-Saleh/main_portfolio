<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('logo_path', 2048)->nullable();
            $table->string('website_url')->nullable();
            $table->string('industry')->nullable();
            $table->string('summary')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
