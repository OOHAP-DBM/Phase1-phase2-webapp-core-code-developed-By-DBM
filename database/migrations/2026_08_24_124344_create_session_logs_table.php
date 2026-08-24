<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_logs', function (Blueprint $table) {
            $table->id();

            // User associated with the session
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Historical role
            $table->string('user_role', 30)
                ->default('system')
                ->index();

            // LOGIN / LOGOUT / LOGIN_FAILED / SESSION_EXPIRED
            $table->string('event', 50)
                ->index();

            // Laravel session identifier
            $table->string('session_id', 255)
                ->nullable()
                ->index();

            // Request/device information
            $table->string('ip_address', 45)
                ->nullable();

            $table->text('user_agent')
                ->nullable();

            // Session lifecycle
            $table->timestamp('login_at')
                ->nullable();

            $table->timestamp('logout_at')
                ->nullable();

            $table->timestamp('last_activity_at')
                ->nullable();

            // Additional device/session data
            $table->json('metadata')
                ->nullable();

            $table->timestamps();

            // User session history
            $table->index(
                ['user_id', 'created_at'],
                'session_logs_user_created_index'
            );

            // Event filtering
            $table->index(
                ['user_role', 'event'],
                'session_logs_role_event_index'
            );

            // Login history
            $table->index(
                ['user_id', 'login_at'],
                'session_logs_user_login_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_logs');
    }
};