<?php

namespace App\Services\Ord;

use Illuminate\Support\Facades\Http;

class Person
{
    public string $uuid;
    public string $inn;
    public string $name;
    public string $type;
    public string $phone;
    public string $rs_url;
    public string $role;

    public function __construct(public OrdService $service) {}

    /**
     * @throws \Exception
     */
    public function create()
    {
        return Http::withHeaders($this->service->getHeaders())
            ->put($this->service::$baseUrl.'/v1/person/'.$this->uuid, [
                'name'  => $this->name,
                'roles' => [ $this->role ],
                'juridical_details' => [
                    'type'  => $this->type,
                    'inn'   => $this->inn,
//                    'phone' => $this->phone,
//                    'rs_url'=> $this->rs_url ?? null,
                ],
            ])->object();
    }

    public function get(string $id): ?object
    {
        return Http::withHeaders($this->service->getHeaders())
            ->get($this->service::$baseUrl.'/v1/person/'.$id)
            ->object();
    }

    public function list(): array
    {
        for ($offset = 0, $limit = 100, $persons = [] ; ; $offset += $limit) {

            $response = Http::withHeaders($this->service->getHeaders())
                ->get($this->service::$baseUrl.'/v1/person', [
                    'limit'  => $limit,
                    'offset' => $offset,
                ]);

            $response = OrdService::parseResponse($response);

            if ($response)
                $persons = array_merge(
                    (array)$response->external_ids,
                    $persons
                );
            else
                break;
        }
        return $persons;
    }

    public function setType(string $type) : static
    {
        $this->type = match ($type) {
            '' => '', //TODO
        };

        return $this;
    }
}
