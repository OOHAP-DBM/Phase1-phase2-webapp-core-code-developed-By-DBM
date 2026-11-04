<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hoarding_transfer_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('users')->cascadeOnDelete();
            $table->string('source_reference', 64);
            $table->string('source_title');
            $table->string('hoarding_type', 10);
            $table->foreignId('imported_hoarding_id')->nullable()->constrained('hoardings')->nullOnDelete();
            $table->timestamps();
            $table->unique(['vendor_id', 'source_reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hoarding_transfer_imports');
    }
};
