<?php

namespace App\Models\Api\Messages;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $table = 'msg_messages';

    protected $fillable = [
        'responsible_user_id',
        'message_id',
        'talk_id',
        'contact_id',
        'text',
        'element_type',
        'element_id',
        'entity_id',
        'type',
        'origin',
        'msg_at',
        'msg_time_at',
        'msg_date_at',
        'lead_created_at',
        'lead_created_at_time',
    ];
}
