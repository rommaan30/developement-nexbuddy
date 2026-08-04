<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Http\Controllers;

use App\Extensions\Chatbot\System\Enums\NotificationTypeEnum;
use App\Extensions\Chatbot\System\Http\Requests\ChatbotNotificationRecipientRequest;
use App\Extensions\Chatbot\System\Models\Chatbot;
use App\Extensions\Chatbot\System\Models\ChatbotNotificationRecipient;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ChatbotNotificationRecipientController extends Controller
{
    private const STATUS_FILTERS = [
        'active'   => 'Active',
        'inactive' => 'Inactive',
    ];

    public function index(Request $request): View
    {
        $chatbots = $this->ownedChatbots($request);
        $chatbotIds = $chatbots->pluck('id')->all();

        $filters = [
            'search'            => trim((string) $request->query('search', '')),
            'status'            => (string) $request->query('status', ''),
            'notification_type' => (string) $request->query('notification_type', ''),
            'chatbot_id'        => (string) $request->query('chatbot_id', ''),
        ];

        $types = NotificationTypeEnum::options();
        $scoped = ChatbotNotificationRecipient::query()->forChatbots($chatbotIds);

        $items = (clone $scoped)
            ->with('chatbot:id,title')
            ->when($filters['search'] !== '', function (Builder $query) use ($filters) {
                $search = $filters['search'];

                $query->where(function (Builder $query) use ($search) {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(array_key_exists($filters['status'], self::STATUS_FILTERS), function (Builder $query) use ($filters) {
                $query->where('is_active', $filters['status'] === 'active');
            })
            ->when(array_key_exists($filters['notification_type'], $types), function (Builder $query) use ($filters) {
                $query->where('notification_type', $filters['notification_type']);
            })
            ->when(
                $filters['chatbot_id'] !== '' && in_array((int) $filters['chatbot_id'], array_map('intval', $chatbotIds), true),
                function (Builder $query) use ($filters) {
                    $query->where('chatbot_id', (int) $filters['chatbot_id']);
                }
            )
            ->latest('id')
            ->paginate($request->integer('per_page', 20))
            ->withQueryString();

        return view('chatbot::notification-recipient.index', [
            'items'          => $items,
            'filters'        => $filters,
            'statusFilters'  => self::STATUS_FILTERS,
            'types'          => $types,
            'chatbots'       => $chatbots,
            'routePrefix'    => $this->routePrefix(),
            'totalCount'     => (clone $scoped)->count(),
            'activeCount'    => (clone $scoped)->active()->count(),
            'title'          => __('Notification Management'),
            'description'    => __('Manage who receives internal notifications for each of your chatbots.'),
        ]);
    }

    public function create(Request $request): View
    {
        return view('chatbot::notification-recipient.edit', [
            'item'        => new ChatbotNotificationRecipient(['is_active' => true]),
            'action'      => route($this->routeName('recipient.store')),
            'method'      => 'POST',
            'types'       => NotificationTypeEnum::options(),
            'chatbots'    => $this->ownedChatbots($request),
            'routePrefix' => $this->routePrefix(),
            'title'       => __('Add Notification Recipient'),
            'description' => __('Add a team member who should receive notifications for one of your chatbots.'),
        ]);
    }

    public function store(ChatbotNotificationRecipientRequest $request): RedirectResponse
    {
        ChatbotNotificationRecipient::query()->create($request->validated());

        return redirect()->route($this->routeName('recipient.index'))
            ->with([
                'type'    => 'success',
                'message' => __('Notification recipient created.'),
            ]);
    }

    public function show(Request $request, ChatbotNotificationRecipient $notificationRecipient): View
    {
        $this->assertOwned($request, $notificationRecipient);
        $notificationRecipient->loadMissing('chatbot:id,title');

        return view('chatbot::notification-recipient.show', [
            'item'        => $notificationRecipient,
            'routePrefix' => $this->routePrefix(),
            'title'       => __('Notification Recipient'),
            'description' => __('Recipient details.'),
        ]);
    }

    public function edit(Request $request, ChatbotNotificationRecipient $notificationRecipient): View
    {
        $this->assertOwned($request, $notificationRecipient);

        return view('chatbot::notification-recipient.edit', [
            'item'        => $notificationRecipient,
            'action'      => route($this->routeName('recipient.update'), $notificationRecipient->getKey()),
            'method'      => 'PUT',
            'types'       => NotificationTypeEnum::options(),
            'chatbots'    => $this->ownedChatbots($request),
            'routePrefix' => $this->routePrefix(),
            'title'       => __('Edit Notification Recipient'),
            'description' => __('Update the recipient details.'),
        ]);
    }

    public function update(ChatbotNotificationRecipientRequest $request, ChatbotNotificationRecipient $notificationRecipient): RedirectResponse
    {
        $this->assertOwned($request, $notificationRecipient);
        $notificationRecipient->update($request->validated());

        return redirect()->route($this->routeName('recipient.index'))
            ->with([
                'type'    => 'success',
                'message' => __('Notification recipient updated.'),
            ]);
    }

    public function toggle(Request $request, ChatbotNotificationRecipient $notificationRecipient): RedirectResponse
    {
        $this->assertOwned($request, $notificationRecipient);

        $notificationRecipient->update([
            'is_active' => ! $notificationRecipient->is_active,
        ]);

        return redirect()
            ->route($this->routeName('recipient.index'), $request->query())
            ->with([
                'type'    => 'success',
                'message' => $notificationRecipient->is_active
                    ? __('Notification recipient enabled.')
                    : __('Notification recipient disabled.'),
            ]);
    }

    public function destroy(Request $request, ChatbotNotificationRecipient $notificationRecipient): RedirectResponse
    {
        $this->assertOwned($request, $notificationRecipient);
        $notificationRecipient->delete();

        return redirect()
            ->route($this->routeName('recipient.index'), $request->query())
            ->with([
                'type'    => 'success',
                'message' => __('Notification recipient deleted.'),
            ]);
    }

    /**
     * @return Collection<int, Chatbot>
     */
    private function ownedChatbots(Request $request): Collection
    {
        return Chatbot::query()
            ->where('user_id', $request->user()?->getKey())
            ->orderBy('title')
            ->get(['id', 'title']);
    }

    private function assertOwned(Request $request, ChatbotNotificationRecipient $recipient): void
    {
        $ownedIds = $this->ownedChatbots($request)->pluck('id')->all();

        if (! in_array((int) $recipient->chatbot_id, array_map('intval', $ownedIds), true)) {
            throw new NotFoundHttpException;
        }
    }

    private function routePrefix(): string
    {
        $name = Route::currentRouteName() ?? '';

        return str_starts_with($name, 'dashboard.chatbot.')
            ? 'dashboard.chatbot.notification-management'
            : 'dashboard.admin.notification-management';
    }

    private function routeName(string $suffix): string
    {
        return $this->routePrefix() . '.' . $suffix;
    }
}
