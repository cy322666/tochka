<?php

namespace App\Services\Ord;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Http;
use Ramsey\Uuid\Uuid;

class Media
{
    public string $uuid;
    public $media_file;
    public $file_name;
    public string $description;

    public function __construct(public OrdService $service) {}

    public function create()
    {
        $multipart = [
            [
                'name'     => 'test',
                'contents' => 'test'
            ], [
                'name'     => 'files',
                'contents' => $this->media_file,
                'filename' => $this->file_name,
                'headers' => ['Content-Type' => 'image/png']
            ],
        ];

        return (new Client())->request(
            'PUT',
            $this->service::$baseUrl.'/v1/media/'.$this->uuid.'?description=opisanieeeeeee',
            array_merge([RequestOptions::MULTIPART => $multipart], ['headers' => $this->service->getHeaders()])
        )->getBody();

//        return Http::withHeaders($this->service->getHeadersMedia())
//            ->put($this->service::$baseUrl.'/v1/media/'.$this->uuid.'?description=opisanieeeeeee', [
////                "description" => $this->description,
//                "media_file"  => $this->media_file,
//            ])->object();
    }

    public function get(string $id): ?object
    {
        return Http::withHeaders($this->service->getHeaders())
            ->get($this->service::$baseUrl.'/v1/media/'.$id)
            ->object();
    }
}
