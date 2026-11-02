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
      Schema::create('offer_versions', function (Blueprint $table) {

    $table->id();

    $table->foreignId('offer_id')
        ->constrained('offers')
        ->cascadeOnDelete();

    $table->unsignedInteger('version_number');

    $table->foreignId('created_by')
        ->constrained('users')
        ->cascadeOnDelete();

    $table->enum('created_by_type', [
        'customer',
        'vendor',
        'admin',
        'system',
    ]);

    $table->enum('status', [
        'draft',
        'sent',
        'accepted',
        'rejected',
    ])->default('draft');

    $table->decimal('subtotal', 12, 2)
        ->default(0);

    $table->decimal('discount_amount', 12, 2)
        ->default(0);

    $table->decimal('tax_amount', 12, 2)
        ->default(0);

    $table->decimal('total_amount', 12, 2)
        ->default(0);

    $table->text('note')
        ->nullable();

    $table->timestamps();

    $table->unique([
        'offer_id',
        'version_number'
    ]);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_versions');
    }
};
