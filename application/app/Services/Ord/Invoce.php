<?php

namespace App\Services\Ord;

use Illuminate\Support\Facades\Http;
use Ramsey\Uuid\Uuid;

class Invoce
{
    public string $uuid;
    public string $contract_external_id;
    public string $date;
    public string $serial;
    public string $date_start;
    public string $date_end;
    public string $amount;
    public string $client_role;
    public string $contractor_role;

    public function __construct(public OrdService $service) {}

    public function create()
    {
        $response = Http::withHeaders($this->service->getHeaders())
            ->put($this->service::$baseUrl.'/v2/invoice/'.$this->uuid.'/header', [
                "contract_external_id" => $this->contract_external_id,
                "date" => $this->date,
                "serial" => $this->serial,
                "date_start" => $this->date_start,
                "date_end" => $this->date_end,
                "amount" => $this->amount,
//                "flags": [
//                "vat_included"
//                ],
                "client_role" => $this->client_role,
                "contractor_role" => $this->contractor_role,
            ]);

        return $this->service->parseResponse($response);
    }

    public function get(string $id)
    {
        $response = Http::withHeaders($this->service->getHeaders())
            ->get($this->service::$baseUrl.'/v1/invoice/'.$id);

        return $this->service->parseResponse($response);
    }

    public function list(): array
    {
        for ($offset = 0, $limit = 1, $persons = [] ; $offset < 3; $offset += $limit) {

            $response = Http::withHeaders($this->service->getHeaders())
                ->get($this->service::$baseUrl.'/v1/invoice', [
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
