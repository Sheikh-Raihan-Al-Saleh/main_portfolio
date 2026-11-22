<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Enquiries from the public contact forms. The optional recipient records
 * which site (studio or founder) the message was sent from.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('recipient_type', 32)->nullable();
            $table->unsignedBigInteger('recipient_id')->nullable();
            $table->string('name', 120);
            $table->string('email');
            $table->string('subject', 180)->nullable();
            $table->text('message');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Inbox filters: unread first, newest first.
            $table->index(['read_at', 'created_at']);
            $table->index(['recipient_type', 'recipient_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
