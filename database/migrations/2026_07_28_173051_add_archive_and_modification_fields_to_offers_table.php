<?php
// database/migrations/2026_07_28_000001_add_archive_and_modification_fields_to_offers_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->after('cancelled_at');
            $table->text('modification_notes')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn(['archived_at', 'modification_notes']);
        });
    }
};
