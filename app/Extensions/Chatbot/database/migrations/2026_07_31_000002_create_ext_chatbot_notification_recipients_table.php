<?php

use App\Extensions\Chatbot\System\Enums\NotificationTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ext_chatbot_notification_recipients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('notification_type')->default(NotificationTypeEnum::ai_bot_enquiry->value);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['email', 'notification_type'], 'ext_chatbot_notification_recipients_unique');
            $table->index(['notification_type', 'is_active'], 'ext_chatbot_notification_recipients_active_index');
        });

        $this->seedConfiguredRecipients();
    }

    public function down(): void
    {
        Schema::dropIfExists('ext_chatbot_notification_recipients');
    }

    /**
     * Carry the previously configured recipients over so notifications keep
     * reaching the same people once the admin module takes over.
     */
    private function seedConfiguredRecipients(): void
    {
        $configured = config('chatbot.enquiry_notifications.recipients', []);

        if (! is_array($configured)) {
            return;
        }

        $rows = [];

        foreach ($configured as $email) {
            if (! is_string($email)) {
                continue;
            }

            $email = trim($email);

            if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                continue;
            }

            $rows[$email] = [
                'name'              => Str::of(Str::before($email, '@'))->replace(['.', '_', '-'], ' ')->title()->value(),
                'email'             => $email,
                'notification_type' => NotificationTypeEnum::ai_bot_enquiry->value,
                'is_active'         => true,
                'created_at'        => now(),
                'updated_at'        => now(),
            ];
        }

        if ($rows === []) {
            return;
        }

        DB::table('ext_chatbot_notification_recipients')->insert(array_values($rows));
    }
};
