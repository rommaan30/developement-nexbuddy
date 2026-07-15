<?php

namespace App\Extensions\Chatbot\System\Http\Controllers;

use App\Extensions\Chatbot\System\Models\ChatbotEnquiry;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class ChatbotEnquiryController extends Controller
{
    public function index(): View
    {
        $chatbotIds = auth()->user()?->externalChatbots()->pluck('id')->toArray() ?? [];
        $totalEnquiries = ChatbotEnquiry::query()
            ->whereIn('chatbot_id', $chatbotIds)
            ->count();

        return view('chatbot::enquiry.index', [
            'hasEnquiries'   => $totalEnquiries > 0,
            'totalEnquiries' => $totalEnquiries,
            'title'       => __('AI Bot Enquiries'),
            'description' => __('View enquiries detected from AI bot conversations.'),
        ]);
    }
}
