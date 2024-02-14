<?php

namespace App\Models\Api\Ord;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;

    protected $table = 'ord_persons';

    protected $fillable = [
        'uuid',
        'create_date',
        'name',
        'roles',
        'juridical_details',
        'type',
        'phone',
        'inn',
        'rs_url',
    ];

    public static function matchType(string $type): string
    {
        return match ($type) {
            'Самозанятый' => 'physical',
            'ИП'  => 'ip',
            'ООО' => 'juridical',
        };
    }
}
