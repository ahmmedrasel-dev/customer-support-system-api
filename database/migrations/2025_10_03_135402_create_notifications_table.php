<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('notifications', function (Blueprint $table) {
      $table->id();
      $table->string('type'); // ticket_created, ticket_assigned, ticket_updated, ticket_deleted
      $table->string('title');
      $table->text('message');
      $table->json('data')->nullable(); // Additional data
      $table->string('action_url')->nullable(); // Where to redirect on click
      $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Who receives notification
      $table->foreignId('sender_id')->nullable()->constrained('users')->onDelete('set null'); // Who sent it
      $table->boolean('read')->default(false);
      $table->timestamp('read_at')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('notifications');
  }
};
