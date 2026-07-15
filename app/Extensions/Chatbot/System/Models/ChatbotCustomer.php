<?php

namespace App\Extensions\Chatbot\System\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatbotCustomer extends Model
{
    protected $table = 'ext_chatbot_customers';

    protected $fillable = [
        'user_id',
        'avatar',
        'name',
        'email',
        'phone',
        'chatbot_id',
        'session_id',
        'country_code',
        'ip_address',
        'chatbot_channel',
        'payload',
    ];

    protected $casts = [
        'payload' => 'json',
    ];

    public function enquiries(): HasMany
    {
        return $this->hasMany(ChatbotEnquiry::class, 'chatbot_customer_id');
    }
}
