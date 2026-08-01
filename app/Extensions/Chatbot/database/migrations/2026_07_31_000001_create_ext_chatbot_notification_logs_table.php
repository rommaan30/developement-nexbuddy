<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ext_chatbot_notification_logs', function (Blueprint $table) {
            $table->id();

            // Intentionally not a foreign key: the log is an audit trail that
            // must survive enquiry deletion and stay decoupled from the module.
            $table->unsignedBigInteger('enquiry_id')->nullable();

            $table->string('channel')->default('mail');
            $table->string('notification_type')->default('enquiry_created');
            $table->string('recipient');
            $table->string('status')->default('pending');
            $table->string('queue_job_id')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index(['enquiry_id', 'channel', 'notification_type', 'recipient'], 'ext_chatbot_notification_logs_lookup_index');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ext_chatbot_notification_logs');
    }
};
