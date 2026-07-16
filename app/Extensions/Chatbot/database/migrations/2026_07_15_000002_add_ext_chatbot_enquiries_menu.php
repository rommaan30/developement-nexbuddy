<?php

use App\Services\Common\MenuService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const MENU_KEY = 'ext_chatbot_enquiries';

    public function up(): void
    {
        if (DB::table('menus')->where('key', self::MENU_KEY)->exists()) {
            app(MenuService::class)->regenerate();

            return;
        }

        $contactsMenu = DB::table('menus')
            ->where('key', 'ext_chatbot_chatbot_customer_article')
            ->first(['id', 'order']);

        DB::table('menus')->insert([
            'parent_id'    => null,
            'key'          => self::MENU_KEY,
            'route'        => 'dashboard.chatbot.enquiries.index',
            'route_slug'   => null,
            'label'        => 'AI Bot Enquiries',
            'icon'         => 'tabler-clipboard-list',
            'svg'          => null,
            'order'        => $contactsMenu?->order ?? 5,
            'is_active'    => true,
            'params'       => json_encode([]),
            'type'         => 'item',
            'extension'    => true,
            'letter_icon'  => false,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        app(MenuService::class)->regenerate();
    }

    public function down(): void
    {
        DB::table('menus')
            ->where('key', self::MENU_KEY)
            ->delete();

        app(MenuService::class)->regenerate();
    }
};
