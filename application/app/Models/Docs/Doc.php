<?php

namespace App\Models\Docs;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doc extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'doc_id',
        'name',
        'path',
        'metadata',
        'type',
        'href',
        'created_at_doc',
        'request_at',
        'lead_id',
        'contact_id',
        'company_id',
        'status',
    ];
}
