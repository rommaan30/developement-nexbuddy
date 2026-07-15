<?php

namespace App\Extensions\Chatbot\System\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotEnquiry extends Model
{
    protected $table = 'ext_chatbot_enquiries';

    protected $fillable = [
        'chatbot_id',
        'conversation_id',
        'chatbot_customer_id',
        'email',
        'phone',
        'company',
        'interest',
        'lead_score',
        'status',
    ];

    protected $casts = [
        'chatbot_id'          => 'integer',
        'conversation_id'     => 'integer',
        'chatbot_customer_id' => 'integer',
        'lead_score'          => 'integer',
    ];

    public function chatbot(): BelongsTo
    {
        return $this->belongsTo(Chatbot::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatbotConversation::class, 'conversation_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(ChatbotCustomer::class, 'chatbot_customer_id');
    }
}
