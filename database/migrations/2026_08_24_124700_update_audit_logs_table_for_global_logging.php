<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {

            $table->string('request_id', 100)
                ->nullable()
                ->after('request_url')
                ->index();

            $table->timestamp('updated_at')
                ->nullable()
                ->after('created_at');
        });

        // Existing default is "admin".
        // Change it to "system" so missing actor information
        // is never incorrectly attributed to an admin.
        DB::statement(
            "ALTER TABLE audit_logs
             MODIFY user_type VARCHAR(255)
             NOT NULL DEFAULT 'system'"
        );
    }

    public function down(): void
    {
        DB::statement(
            "ALTER TABLE audit_logs
             MODIFY user_type VARCHAR(255)
             NOT NULL DEFAULT 'admin'"
        );

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['request_id']);

            $table->dropColumn([
                'request_id',
                'updated_at',
            ]);
        });
    }
};