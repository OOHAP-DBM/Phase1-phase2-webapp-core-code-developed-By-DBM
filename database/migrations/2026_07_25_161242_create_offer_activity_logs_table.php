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
       Schema::create('offer_activity_logs', function (Blueprint $table) {

    $table->id();

    $table->foreignId('offer_id')
        ->constrained('offers')
        ->cascadeOnDelete();

    $table->foreignId('offer_version_id')
        ->nullable()
        ->constrained('offer_versions')
        ->nullOnDelete();

    $table->foreignId('actor_id')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete();

    $table->enum('actor_type', [
        'customer',
        'vendor',
        'admin',
        'system',
    ]);

    $table->string('action');

    $table->text('description')
        ->nullable();

    $table->json('metadata')
        ->nullable();

    $table->timestamps();

    $table->index([
        'offer_id',
        'created_at'
    ]);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_activity_logs');
    }
};
