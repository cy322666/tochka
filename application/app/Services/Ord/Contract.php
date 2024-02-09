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
    public string $action_type;
    public string $subject_type;
    public string $parent_contract_external_id;
    public string $amount;//"500.5"

    public function __construct(public OrdService $service) {}

    public function create()
    {
        $response = Http::withHeaders($this->service->getHeaders())
            ->put($this->service::$baseUrl.'/v1/contract/'.$this->uuid, [
                "type" => $this->type,
                "client_external_id" => $this->client_external_id,
                "contractor_external_id" => $this->contractor_external_id,
                "date" => $this->date,
                "serial" => $this->serial,
//                "action_type" => $this->action_type,
                "subject_type" => $this->subject_type,//TODO
                "parent_contract_external_id" => $this->parent_contract_external_id,
//                "amount" => $this->amount,
            ]);

        return $this->service->parseResponse($response);
    }

    public function get(string $id)
    {
        $response = Http::withHeaders($this->service->getHeaders())
            ->get($this->service::$baseUrl.'/v1/contract/'.$id);

        return $this->service->parseResponse($response);
    }

    public function list(): array
    {
        for ($offset = 0, $limit = 1, $persons = [] ; $offset < 3; $offset += $limit) {

            $response = Http::withHeaders($this->service->getHeaders())
                ->get($this->service::$baseUrl.'/v1/contract', [
                    'limit'  => $limit,
                    'offset' => $offset,
                ]);

            $persons = array_merge(
                (array)($this->service->parseResponse($response))->external_ids,
                $persons
            );
        }

        return $persons;
    }
}
