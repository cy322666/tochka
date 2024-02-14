<?php

namespace App\Models\Api\Ord;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'ord_transactions';

    protected $fillable = [
        'person_uuid',
        'contract_uuid',
        'creative_uuid',
        'lead_id',
        'contact_id',
        'company_id',
        'status',
        'erid',
        'marker',
        'creative_uuid',
        'parent_contract_external_id',
        'contract_serial',
    ];
}
