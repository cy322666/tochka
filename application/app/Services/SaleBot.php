<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SaleBot
{
    public static string $token;

    private static string $baseUrl = 'https://chatter.salebot.pro/api/';

    public function unsubscribe(int $listId, int $clientId): ?object
    {
        return Http::post(static::$baseUrl.static::$token.'/remove_from_list', [
            'list_id' => $listId,
            'clients' => $clientId,
        ])->object();
    }
}
