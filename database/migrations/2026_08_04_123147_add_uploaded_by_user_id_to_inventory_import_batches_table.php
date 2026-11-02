<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inventory_import_batches', function (Blueprint $table) {

            $table->foreignId('uploaded_by_user_id')
                ->nullable()
                ->after('vendor_id')
                ->constrained('users')
                ->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_import_batches', function (Blueprint $table) {
            //
        });
    }
};
