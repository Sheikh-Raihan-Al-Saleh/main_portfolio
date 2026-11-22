<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Social profiles for a studio or a founder (polymorphic owner).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->string('owner_type', 32);
            $table->unsignedBigInteger('owner_id');
            $table->string('platform', 32);
            $table->string('label', 60)->nullable();
            $table->string('url');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            // One link per platform per owner. The prefix also serves owner lookups.
            $table->unique(['owner_type', 'owner_id', 'platform']);
            $table->index(['owner_type', 'owner_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_links');
    }
};
