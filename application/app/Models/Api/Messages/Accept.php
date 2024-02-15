<?php

namespace App\Models\Api\Messages;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accept extends Model
{
    use HasFactory;

    protected $table = 'msg_accepts';

    protected $fillable = [
        'lead_id',
        'lead_created_at',
        'first_out',
        'talk_id',
        'time',
        'time_at',
        'responsible_user_id',
    ];
}
