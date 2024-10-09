<?php

namespace App\Models\Api\Sheets;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'sheets_transactions';

    protected $fillable = [
        'link_id',
        'lead_id',
        'url',
        'name',
        'check_1',
        'count_1',
        'check_2',
        'count_2',
        'status',
    ];
}
