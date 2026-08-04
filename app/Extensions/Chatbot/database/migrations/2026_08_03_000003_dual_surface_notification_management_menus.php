<?php

use App\Services\Common\MenuService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Keep the admin menu key on the admin surface for Super Admin.
        DB::table('menus')->updateOrInsert(['key' => 'ext_chatbot_notification_recipients'], [
            'parent_id'   => null,
            'route'       => 'dashboard.admin.notification-management.recipient.index',
            'route_slug'  => null,
            'label'       => 'Notification Management',
            'icon'        => 'tabler-bell-cog',
            'svg'         => null,
            'order'       => 36,
            'is_active'   => true,
            'params'      => json_encode([]),
            'type'        => 'item',
            'extension'   => true,
            'letter_icon' => false,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Clients use the chatbot panel entry.
        DB::table('menus')->updateOrInsert(['key' => 'ext_chatbot_notification_recipients_panel'], [
            'parent_id'   => null,
            'route'       => 'dashboard.chatbot.notification-management.recipient.index',
            'route_slug'  => null,
            'label'       => 'Notification Management',
            'icon'        => 'tabler-bell-cog',
            'svg'         => null,
            'order'       => 6,
            'is_active'   => true,
            'params'      => json_encode([]),
            'type'        => 'item',
            'extension'   => true,
            'letter_icon' => false,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        app(MenuService::class)->regenerate();
    }

    public function down(): void
    {
        DB::table('menus')->where('key', 'ext_chatbot_notification_recipients_panel')->delete();

        DB::table('menus')->where('key', 'ext_chatbot_notification_recipients')->update([
            'route'      => 'dashboard.admin.notification-management.recipient.index',
            'updated_at' => now(),
        ]);

        app(MenuService::class)->regenerate();
    }
};
