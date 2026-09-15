<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_partner_information', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            // e.g. "Vendor & Partner Information"

            $table->longText('content');
            // Full vendor/partner information content

            $table->boolean('is_active')->default(true);
            // Only active information visible on frontend

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_partner_information');
    }
};
