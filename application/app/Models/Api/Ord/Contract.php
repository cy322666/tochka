<?php

namespace App\Models\Api\Ord;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $table = 'ord_contracts';

    protected $fillable = [
        'uuid',
        'create_date',
        'type',
        'client_external_id',
        'contractor_external_id',
        'subject_type',
        'date',
        'serial',
        'parent_contract_external_id',
    ];

    public static function getSerialName(string $parentSerial)
    {
        $parent = Contract::query()
            ->where('type', 'service')
            ->where('serial', $parentSerial)
            ->first();

        $lastContractForParent = Contract::query()
            ->where('parent_contract_external_id', $parent->uuid)
            ->orderBy('id', 'DESC')
            ->first();

        if ($lastContractForParent) {

            $arraySerial = explode('_', $lastContractForParent->serial);

            $number = $arraySerial[1];
            $serial = $arraySerial[0];

            return $serial.'_'.++$number;
        } else
            return $parentSerial.'_1';
    }
}
