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

    public function create()
    {
        $response = Http::withHeaders($this->service->getHeaders())
            ->put($this->service::$baseUrl.'/v1/person/'.$this->uuid, [
                'name'  => $this->name,
                'roles' => [ $this->role ],
                'juridical_details' => [
                    'type'  => $this->type,
                    'inn'   => $this->inn,
                    'phone' => $this->phone,
//                    'rs_url'=> $this->rs_url ?? null,
                ],
            ]);

        return $this->service->parseResponse($response);
    }

    public function get(string $id)
    {
        $response = Http::withHeaders($this->service->getHeaders())
            ->get($this->service::$baseUrl.'/v1/person/'.$id);

        return $this->service->parseResponse($response);
    }

    public function list(): array
    {
        for ($offset = 0, $limit = 100, $persons = [] ; $offset < 200 ; $offset += $limit) {

            $response = Http::withHeaders($this->service->getHeaders())
                ->get($this->service::$baseUrl.'/v1/person', [
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

    public function setType(string $type) : static
    {
        $this->type = match ($type) {
            '' => '', //TODO
        };

        return $this;
    }

        /*
 * {
"": "Иванов Сергей Петрович",
"": [
"advertiser",
"agency"
],
"": {
"": "",
"": "727718317746",
"": "+7(495)709-56-39"
}
}
 */

}
