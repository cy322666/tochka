<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SaleBot
{
    private static string $baseUrl = 'https://chatter.salebot.pro/api/';

    public function __construct(public string $token) {}

    public function unsubscribe(int $listId, int $clientId): ?object
    {
        return Http::post(static::$baseUrl.$this->token.'/remove_from_list', [
            'list_id' => $listId,
            'clients' => $clientId,
        ])->object();
    }
}
