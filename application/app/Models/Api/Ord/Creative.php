<?php

namespace App\Models\Api\Ord;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Creative extends Model
{
    use HasFactory;

    protected $table = 'ord_creative';

    protected $fillable = [
        'uuid',
        'name',
        'media_uuid',
        'erid',
        'contract_external_id',
    ];
}
