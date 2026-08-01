<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Http\Controllers;

use App\Extensions\Chatbot\System\Enums\NotificationTypeEnum;
use App\Extensions\Chatbot\System\Http\Requests\ChatbotNotificationRecipientRequest;
use App\Extensions\Chatbot\System\Models\ChatbotNotificationRecipient;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ChatbotNotificationRecipientController extends Controller
{
    private const STATUS_FILTERS = [
        'active'   => 'Active',
        'inactive' => 'Inactive',
    ];

    public function index(Request $request): View
    {
        $filters = [
            'search'            => trim((string) $request->query('search', '')),
            'status'            => (string) $request->query('status', ''),
            'notification_type' => (string) $request->query('notification_type', ''),
        ];

        $types = NotificationTypeEnum::options();

        $items = ChatbotNotificationRecipient::query()
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
            ->latest('id')
            ->paginate($request->integer('per_page', 20))
            ->withQueryString();

        return view('chatbot::notification-recipient.index', [
            'items'          => $items,
            'filters'        => $filters,
            'statusFilters'  => self::STATUS_FILTERS,
            'types'          => $types,
            'totalCount'     => ChatbotNotificationRecipient::query()->count(),
            'activeCount'    => ChatbotNotificationRecipient::query()->active()->count(),
            'title'          => __('Notification Management'),
            'description'    => __('Manage who receives internal notifications for new enquiries and other events.'),
        ]);
    }

    public function create(): View
    {
        return view('chatbot::notification-recipient.edit', [
            'item'        => new ChatbotNotificationRecipient(['is_active' => true]),
            'action'      => route('dashboard.admin.notification-management.recipient.store'),
            'method'      => 'POST',
            'types'       => NotificationTypeEnum::options(),
            'title'       => __('Add Notification Recipient'),
            'description' => __('Add a team member who should receive notifications.'),
        ]);
    }

    public function store(ChatbotNotificationRecipientRequest $request): RedirectResponse
    {
        ChatbotNotificationRecipient::query()->create($request->validated());

        return redirect()->route('dashboard.admin.notification-management.recipient.index')
            ->with([
                'type'    => 'success',
                'message' => __('Notification recipient created.'),
            ]);
    }

    public function show(ChatbotNotificationRecipient $notificationRecipient): View
    {
        return view('chatbot::notification-recipient.show', [
            'item'        => $notificationRecipient,
            'title'       => __('Notification Recipient'),
            'description' => __('Recipient details.'),
        ]);
    }

    public function edit(ChatbotNotificationRecipient $notificationRecipient): View
    {
        return view('chatbot::notification-recipient.edit', [
            'item'        => $notificationRecipient,
            'action'      => route('dashboard.admin.notification-management.recipient.update', $notificationRecipient->getKey()),
            'method'      => 'PUT',
            'types'       => NotificationTypeEnum::options(),
            'title'       => __('Edit Notification Recipient'),
            'description' => __('Update the recipient details.'),
        ]);
    }

    public function update(ChatbotNotificationRecipientRequest $request, ChatbotNotificationRecipient $notificationRecipient): RedirectResponse
    {
        $notificationRecipient->update($request->validated());

        return redirect()->route('dashboard.admin.notification-management.recipient.index')
            ->with([
                'type'    => 'success',
                'message' => __('Notification recipient updated.'),
            ]);
    }

    public function toggle(Request $request, ChatbotNotificationRecipient $notificationRecipient): RedirectResponse
    {
        $notificationRecipient->update([
            'is_active' => ! $notificationRecipient->is_active,
        ]);

        return redirect()
            ->route('dashboard.admin.notification-management.recipient.index', $request->query())
            ->with([
                'type'    => 'success',
                'message' => $notificationRecipient->is_active
                    ? __('Notification recipient enabled.')
                    : __('Notification recipient disabled.'),
            ]);
    }

    public function destroy(Request $request, ChatbotNotificationRecipient $notificationRecipient): RedirectResponse
    {
        $notificationRecipient->delete();

        return redirect()
            ->route('dashboard.admin.notification-management.recipient.index', $request->query())
            ->with([
                'type'    => 'success',
                'message' => __('Notification recipient deleted.'),
            ]);
    }
}
