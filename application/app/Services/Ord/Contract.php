<?php

namespace App\Services\Ord;

use Illuminate\Support\Facades\Http;
use Ramsey\Uuid\Uuid;

class Contract
{
    public string $type;
    public string $uuid;
    public string $client_external_id;
    public string $contractor_external_id;
    public string $date;//"2022-12-01"
    public string $serial;
//    public string $action_type;
    public string $subject_type;
    public ?string $parent_contract_external_id = null;
//    public string $amount;//"500.5"

    public function __construct(public OrdService $service) {}

    public function create()
    {
        return Http::withHeaders($this->service->getHeaders())
            ->put($this->service::$baseUrl.'/v1/contract/'.$this->uuid, [
                "type" => $this->type,
                "client_external_id" => $this->client_external_id,
                "contractor_external_id" => $this->contractor_external_id,
                "date" => $this->date,
                "serial" => $this->serial,
                "subject_type" => $this->subject_type,
                "parent_contract_external_id" => $this->parent_contract_external_id,
            ])->object();
    }

    public function get(string $id): ?object
    {
        return Http::withHeaders($this->service->getHeaders())
            ->get($this->service::$baseUrl.'/v1/contract/'.$id)
            ->object();
    }

    public function list(): array
    {
        $limit = \App\Models\Api\Ord\Contract::query()->count();

        for ($limit -= 500, $persons = [] ; ; $offset += $limit) {

            $response = Http::withHeaders($this->service->getHeaders())
                ->get($this->service::$baseUrl.'/v1/contract', [
                    'limit'  => $limit,
                    'offset' => $offset,
                ]);

            $response = OrdService::parseResponse($response);

            if ($response)
                $persons = array_merge($response->external_ids, $persons);
            else
                break;
        }
        return $persons;
    }
}
