<?php

namespace App\Models\Api\Messages;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Talk extends Model
{
    use HasFactory;

    protected $table = 'msg_talks';

    protected $fillable = [
        'out_id',
        'in_id',
        'out_at',
        'in_at',
        'time',
        'status',
        'talk_id',
        'responsible_user_id',
    ];
}
