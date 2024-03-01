<?php

namespace App\Models\Api\Ord;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pad extends Model
{
    use HasFactory;

    protected $table = 'ord_pads';

    protected $fillable = [
        'uuid',
        "create_date",
        "person_external_id",
        "is_owner",
        "type",
        "name",
        "url",
    ];
}
