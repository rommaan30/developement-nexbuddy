<?php

use App\Services\Common\MenuService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const MENU_KEY = 'ext_chatbot_notification_recipients';

    public function up(): void
    {
        // Chatbot owners (not only platform admins) must manage their own
        // recipients, matching AI Bot Enquiries panel access.
        DB::table('menus')->where('key', self::MENU_KEY)->update([
            'route'      => 'dashboard.chatbot.notification-management.recipient.index',
            'order'      => 6,
            'updated_at' => now(),
        ]);

        app(MenuService::class)->regenerate();
    }

    public function down(): void
    {
        DB::table('menus')->where('key', self::MENU_KEY)->update([
            'route'      => 'dashboard.admin.notification-management.recipient.index',
            'order'      => 36,
            'updated_at' => now(),
        ]);

        app(MenuService::class)->regenerate();
    }
};
