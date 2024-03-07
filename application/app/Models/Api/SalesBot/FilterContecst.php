<?php

namespace App\Models\Api\SalesBot;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilterContecst extends Model
{
    use HasFactory;

    protected $table = 'filter_contecsts';

    protected $fillable = [
        'lead_id',
        'contact_id',
        'client_id',
        'list_id',
        'salesbot_id',
        'status_id',
        'pipeline_id',
        'in_sales',
        'status',
    ];
}
