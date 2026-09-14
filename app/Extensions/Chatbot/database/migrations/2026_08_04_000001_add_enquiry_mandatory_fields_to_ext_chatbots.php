<?php

use App\Extensions\Chatbot\System\Services\Enquiry\ChatbotMandatoryFields;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ext_chatbots', function (Blueprint $table) {
            $table->json('enquiry_mandatory_fields')->nullable()->after('enquiry_interests');
        });

        $rajeshUserId = DB::table('users')
            ->where('email', 'rajeshdeshmukh108@gmail.com')
            ->value('id');

        // Only chatbots that need non-software qualification get an explicit list.
        // Everyone else stays null → MissingFieldsManager uses the legacy software fallback.
        if ($rajeshUserId !== null) {
            DB::table('ext_chatbots')
                ->where('user_id', $rajeshUserId)
                ->update([
                    'enquiry_mandatory_fields' => json_encode(ChatbotMandatoryFields::defaultMedical()),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('ext_chatbots', function (Blueprint $table) {
            $table->dropColumn('enquiry_mandatory_fields');
        });
    }
};
