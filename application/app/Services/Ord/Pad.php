<?php

namespace App\Services\Ord;

use Illuminate\Support\Facades\Http;
use Ramsey\Uuid\Uuid;

class Pad
{
    public string $uuid;
    public string $url;
    public string $name;
    public string $type;
    public string $is_owner;
    public string $person_external_id;
    public string $create_date;

    public function __construct(public OrdService $service) {}

    public function create()
    {
        return Http::withHeaders($this->service->getHeaders())
            ->put($this->service::$baseUrl.'/v1/pad/'.$this->uuid, [
//                "create_date" => $this->create_date,
                "person_external_id" => $this->person_external_id,
                "is_owner" => $this->is_owner,
                "type" => $this->type,
                "name" => $this->name,
                "url" => $this->url,
            ])->object();
    }

    public function get(string $id): ?object
    {
        return Http::withHeaders($this->service->getHeaders())
            ->get($this->service::$baseUrl.'/v1/pad/'.$id)
            ->object();
    }

    public function list(): array
    {
        for ($offset = 0, $limit = 100, $pads = [] ; ; $offset += $limit) {

            $response = Http::withHeaders($this->service->getHeaders())
                ->get($this->service::$baseUrl.'/v1/pad', [
                    'limit'  => $limit,
                    'offset' => $offset,
                ]);

            $response = OrdService::parseResponse($response);

            if ($response)
                $pads = array_merge(
                    (array)$response->external_ids,
                    $pads
                );
            else
                break;
        }

        return $pads;
    }
}
