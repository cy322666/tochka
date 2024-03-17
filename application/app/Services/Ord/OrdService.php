<?php

namespace App\Services\Ord;

use Illuminate\Http\Client\Response;

class OrdService
{
    public static string $env;
    public static string $token;
    public static string $baseUrl;

    public function __construct(string $env = 'local')
    {
        static::$env = $env;
        static::$baseUrl = static::$env == 'local' ? 'https://api-sandbox.ord.vk.com' : 'https://api.ord.vk.com';
        static::$token   = static::$env == 'local' ? env('ORD_TOKEN_LOCAL') : env('ORD_TOKEN_PROD');
    }

    public function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.static::$token,
            'Content-type'  => 'application/json'
        ];
    }

    public function getHeadersMedia(): array
    {
        return [
            'Authorization' => 'Bearer '.static::$token,
            'Content-Type'  => 'multipart/form-data'
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

    public function invoice(): Invoice
    {
        return new Invoice($this);
    }

    public function pad(): Pad
    {
        return new Pad($this);
    }

    public function media(): Media
    {
        return new Media($this);
    }

    public static function parseResponse(Response $response)
    {
        $body = json_decode($response->body());

        if (empty($body->external_ids) || count($body->external_ids) == 0)

            return false;
        else
            return $body;
    }
}
