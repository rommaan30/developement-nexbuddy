<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ext_chatbot_enquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chatbot_id')->constrained('ext_chatbots')->cascadeOnDelete();
            $table->foreignId('conversation_id')->constrained('ext_chatbot_conversations')->cascadeOnDelete();
            $table->foreignId('chatbot_customer_id')->nullable()->constrained('ext_chatbot_customers')->nullOnDelete();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('interest')->nullable();
            $table->integer('lead_score')->default(0);
            $table->string('status')->default('new');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ext_chatbot_enquiries');
    }
};
