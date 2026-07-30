<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ext_chatbot_enquiries', function (Blueprint $table) {
            $table->string('visitor_name')->nullable()->after('chatbot_customer_id');
        });
    }

    public function down(): void
    {
        Schema::table('ext_chatbot_enquiries', function (Blueprint $table) {
            $table->dropColumn('visitor_name');
        });
    }
};
