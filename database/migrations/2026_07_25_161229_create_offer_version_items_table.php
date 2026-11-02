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
      Schema::create('offer_version_items', function (Blueprint $table) {

    $table->id();

    $table->foreignId('offer_version_id')
        ->constrained('offer_versions')
        ->cascadeOnDelete();

    $table->foreignId('enquiry_item_id')
        ->nullable()
        ->constrained('enquiry_items')
        ->nullOnDelete();

    $table->foreignId('hoarding_id')
        ->constrained('hoardings')
        ->cascadeOnDelete();

    $table->enum('hoarding_type', [
        'ooh',
        'dooh',
    ]);

    $table->unsignedBigInteger('package_id')
        ->nullable();

    $table->string('package_type')
        ->nullable();

    $table->string('package_label')
        ->nullable();

    $table->date('start_date')
        ->nullable();

    $table->date('end_date')
        ->nullable();

    $table->integer('duration_months')
        ->nullable();

    $table->decimal('unit_price', 12, 2)
        ->default(0);

    $table->decimal('discount_amount', 12, 2)
        ->default(0);

    $table->decimal('tax_amount', 12, 2)
        ->default(0);

    $table->decimal('final_price', 12, 2)
        ->default(0);

    $table->json('services')
        ->nullable();

    $table->json('meta')
        ->nullable();

    $table->timestamps();

    $table->index([
        'offer_version_id',
        'hoarding_id'
    ]);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_version_items');
    }
};
