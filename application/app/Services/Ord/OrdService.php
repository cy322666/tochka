<?php

namespace App\Services\Ord;


use Illuminate\Http\Client\Response;

class OrdService
{
    public static string $baseUrl = 'https://api-sandbox.ord.vk.com'; //'https://api.ord.vk.com'

    public function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.env('ORD_TOKEN'),
            'Content-type'  => 'application/json'
        ];
    }

    public function person(): Person
    {
        return new Person($this);
    }

    public function contract(): Contract
    {
        return new Contract($this);
    }

    public function creative(): Creative
    {
        return new Creative($this);
    }

    public function parseResponse(Response $response)
    {
        return json_decode($response->body());
    }

}
