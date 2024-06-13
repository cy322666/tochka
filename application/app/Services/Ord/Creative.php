<?php

namespace App\Services\Ord;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class Creative
{
    public string $uuid;
    public string $contract_external_id;
    public string $okveds = '85.41';
    public string $name;
    public string $brand;
    public string $category;
    public string $description;
    public string $pay_type;
    public string $form;
    public string $targeting;
    public array $target_urls;
    public string $url;
    public array $texts;
    public array $media_external_ids;
    public array $media_urls;
    public string $flags;

    public function __construct(public OrdService $service) {}

    public function create()
    {
        $body = [
            "contract_external_id" => $this->contract_external_id,
            "okveds" => [$this->okveds],
            "name" => $this->name,
            "brand" => $this->brand,
//                "category" => $this->category,
//                "description" => $this->description,
            "pay_type" => $this->pay_type,
            "form" => $this->form,
//                "targeting" => $this->targeting,
//            "url" => $this->target_urls,
            "url" => $this->url,
            "texts" => $this->texts,
            "media_external_ids" => $this->media_external_ids,
//            "media_urls" => $this->media_urls,
        ];

        Log::debug(__METHOD__, $body);

        return Http::withHeaders($this->service->getHeaders())
            ->put($this->service::$baseUrl.'/v1/creative/'.$this->uuid, $body)->object();
    }

    public function get(string $id)
    {
        $response = Http::withHeaders($this->service->getHeaders())
            ->get($this->service::$baseUrl.'/v1/creative/'.$id);

        return $this->service->parseResponse($response);
    }

    public function list(): array
    {
        for ($offset = 0, $limit = 1, $persons = [] ; ; $offset += $limit) {

            $response = Http::withHeaders($this->service->getHeaders())
                ->get($this->service::$baseUrl.'/v1/creative', [
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
