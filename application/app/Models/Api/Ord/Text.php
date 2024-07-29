<?php

namespace App\Models\Api\Ord;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Text extends Model
{
    use HasFactory;

    protected $table = 'ord_texts';

    protected $fillable = [
        'text',
        'key',
        'media',
    ];
}
