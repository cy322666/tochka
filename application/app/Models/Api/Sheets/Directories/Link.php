<?php

namespace App\Models\Api\Sheets\Directories;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    use HasFactory;

    protected $table = 'sheets_links';

    protected $fillable = [
        'link_id',
        'name',
        'url',
        'type'
    ];
}
