<?php

use App\Enums\Roles;
use App\Services\Common\MenuService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const MENU_KEY = 'ext_chatbot_notification_recipients';

    public function up(): void
    {
        DB::table('menus')->updateOrInsert(['key' => self::MENU_KEY], [
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

        $this->grantAdminPermission();

        app(MenuService::class)->regenerate();
    }

    public function down(): void
    {
        DB::table('menus')
            ->where('key', self::MENU_KEY)
            ->delete();

        $permissionId = DB::table('permissions')->where('name', self::MENU_KEY)->value('id');

        if ($permissionId !== null) {
            DB::table('role_has_permissions')->where('permission_id', $permissionId)->delete();
            DB::table('permissions')->where('id', $permissionId)->delete();
        }

        Cache::forget('admin_permissions');

        app(MenuService::class)->regenerate();
    }

    /**
     * The module is admin only, so it needs a permission entry that a super
     * admin can grant or revoke from the User Permissions screen.
     */
    private function grantAdminPermission(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('permissions')) {
            return;
        }

        $permissionId = DB::table('permissions')->where('name', self::MENU_KEY)->value('id');

        if ($permissionId === null) {
            $permissionId = DB::table('permissions')->insertGetId([
                'name'       => self::MENU_KEY,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $adminRoleId = DB::table('roles')->where('name', Roles::ADMIN->value)->value('id');

        if ($adminRoleId !== null) {
            $alreadyGranted = DB::table('role_has_permissions')
                ->where('permission_id', $permissionId)
                ->where('role_id', $adminRoleId)
                ->exists();

            if (! $alreadyGranted) {
                DB::table('role_has_permissions')->insert([
                    'permission_id' => $permissionId,
                    'role_id'       => $adminRoleId,
                ]);
            }
        }

        Cache::forget('admin_permissions');
    }
};
