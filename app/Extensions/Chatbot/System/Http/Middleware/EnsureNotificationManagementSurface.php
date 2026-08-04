<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Super Admin uses the admin Notification Management URL.
 * Client owners use the chatbot panel URL. Both hit the same controller.
 */
class EnsureNotificationManagementSurface
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $routeName = $request->route()?->getName() ?? '';

        if ($user === null || $routeName === '') {
            return $next($request);
        }

        $isAdminSurface = str_starts_with($routeName, 'dashboard.admin.notification-management.');
        $isChatbotSurface = str_starts_with($routeName, 'dashboard.chatbot.notification-management.');

        $parameters = [];

        try {
            $parameters = $request->route()?->parameters() ?? [];
        } catch (Throwable) {
            $parameters = [];
        }

        if ($isAdminSurface && ! $user->isSuperAdmin()) {
            return redirect()->route(
                $this->counterpart($routeName, 'dashboard.chatbot.notification-management.'),
                array_merge($parameters, $request->query())
            );
        }

        if ($isChatbotSurface && $user->isSuperAdmin()) {
            return redirect()->route(
                $this->counterpart($routeName, 'dashboard.admin.notification-management.'),
                array_merge($parameters, $request->query())
            );
        }

        return $next($request);
    }

    private function counterpart(string $routeName, string $targetPrefix): string
    {
        $suffix = (string) preg_replace(
            '/^dashboard\.(admin|chatbot)\.notification-management\./',
            '',
            $routeName
        );

        return $targetPrefix . $suffix;
    }
}
