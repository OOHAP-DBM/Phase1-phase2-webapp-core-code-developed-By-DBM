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
       Schema::table('offers', function (Blueprint $table) {

    $table->foreignId('customer_id')
        ->nullable()
        ->after('vendor_id')
        ->constrained('users')
        ->nullOnDelete();

    $table->string('offer_number')
        ->nullable()
        ->unique()
        ->after('id');

    $table->foreignId('current_version_id')
        ->nullable()
        ->after('offer_number');

    $table->timestamp('accepted_at')
        ->nullable();

    $table->timestamp('rejected_at')
        ->nullable();

    $table->timestamp('cancelled_at')
        ->nullable();

    $table->index([
        'enquiry_id',
        'vendor_id'
    ]);
});
    }

    /**
     * Reverse the migrations.
     */
     public function down(): void
{
    Schema::table('offers', function (Blueprint $table) {
        $table->dropForeign(['customer_id']);
        $table->dropColumn([
            'customer_id',
            'offer_number',
            'current_version_id',
            'accepted_at',
            'rejected_at',
            'cancelled_at',
        ]);

        $table->dropIndex(['enquiry_id', 'vendor_id']);
    });
}


};
