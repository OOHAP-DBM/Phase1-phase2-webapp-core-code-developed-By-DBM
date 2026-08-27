<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('email_settings', function (Blueprint $table) {
            $table->id();

            // Mail configuration
            $table->string('mailer')->default('smtp');
            $table->string('host')->nullable();
            $table->unsignedInteger('port')->nullable();
            $table->string('encryption')->nullable();

            // Authentication
            $table->string('username')->nullable();
            $table->text('password')->nullable();

            // Sender
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();

            // Reply To
            $table->string('reply_to_name')->nullable();
            $table->string('reply_to_email')->nullable();

            // Status
            $table->boolean('is_active')->default(true);

            // Audit
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_settings');
    }
};