<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // User who performed the action
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Historical role at the time of action
            $table->string('user_role', 30)
                ->default('system')
                ->index();

            // Action performed
            $table->string('action', 100)
                ->index();

            // Application/module where action happened
            $table->string('module', 100)
                ->nullable()
                ->index();

            // Polymorphic subject
            $table->string('subject_type')
                ->nullable();

            $table->unsignedBigInteger('subject_id')
                ->nullable();

            // Human-readable description
            $table->text('description')
                ->nullable();

            // Additional information
            $table->json('metadata')
                ->nullable();

            // Request information
            $table->string('ip_address', 45)
                ->nullable();

            $table->text('user_agent')
                ->nullable();

            $table->timestamps();

            // Polymorphic subject lookup
            $table->index(
                ['subject_type', 'subject_id'],
                'activity_logs_subject_index'
            );

            // Useful for user activity history
            $table->index(
                ['user_id', 'created_at'],
                'activity_logs_user_created_index'
            );

            // Useful for module/action filtering
            $table->index(
                ['module', 'action'],
                'activity_logs_module_action_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};