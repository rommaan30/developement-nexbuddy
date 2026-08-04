<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ext_chatbot_notification_recipients', function (Blueprint $table) {
            $table->unsignedBigInteger('chatbot_id')->nullable()->after('id');
        });

        $this->backfillNexgenoRecipients();

        Schema::table('ext_chatbot_notification_recipients', function (Blueprint $table) {
            $table->dropUnique('ext_chatbot_notification_recipients_unique');

            $table->foreign('chatbot_id')
                ->references('id')
                ->on('ext_chatbots')
                ->cascadeOnDelete();

            // Same email may serve different chatbots; uniqueness is per bot.
            $table->unique(
                ['chatbot_id', 'email', 'notification_type'],
                'ext_chatbot_notification_recipients_bot_unique'
            );

            $table->index(['chatbot_id', 'notification_type', 'is_active'], 'ext_chatbot_notification_recipients_bot_active');
        });

        // Orphans cannot receive mail and must not remain after backfill.
        DB::table('ext_chatbot_notification_recipients')
            ->whereNull('chatbot_id')
            ->delete();
    }

    public function down(): void
    {
        Schema::table('ext_chatbot_notification_recipients', function (Blueprint $table) {
            $table->dropForeign(['chatbot_id']);
            $table->dropUnique('ext_chatbot_notification_recipients_bot_unique');
            $table->dropIndex('ext_chatbot_notification_recipients_bot_active');
            $table->dropColumn('chatbot_id');

            $table->unique(['email', 'notification_type'], 'ext_chatbot_notification_recipients_unique');
        });
    }

    /**
     * Existing rows were seeded for the Nexgeno platform bot. Attach them to
     * the primary Nexgeno-owned chatbot so production delivery keeps working.
     */
    private function backfillNexgenoRecipients(): void
    {
        $nexgenoUserId = DB::table('users')
            ->where('email', 'admin@nexgeno.in')
            ->value('id');

        $chatbotId = $nexgenoUserId === null
            ? null
            : DB::table('ext_chatbots')
                ->where('user_id', $nexgenoUserId)
                ->orderBy('id')
                ->value('id');

        if ($chatbotId === null) {
            $chatbotId = DB::table('ext_chatbots')
                ->where(function ($query) {
                    $query
                        ->where('title', 'like', '%Nexbuddy%')
                        ->orWhere('title', 'like', '%Nexgeno%');
                })
                ->orderBy('id')
                ->value('id');
        }

        if ($chatbotId === null) {
            return;
        }

        DB::table('ext_chatbot_notification_recipients')
            ->whereNull('chatbot_id')
            ->update(['chatbot_id' => $chatbotId]);
    }
};
