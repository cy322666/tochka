<?php

namespace App\Services\Ord;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
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

    /**
     * @throws GuzzleException
     */
    public function create()
    {
        return (new Client())->request(
            'PUT',
            $this->service::$baseUrl.'/v1/media/'.$this->uuid.'?description='.$this->description, [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->service::$token,
                ],
                'multipart' => [
                    [
                        'name'     => $this->file_name,
                        'contents' => $this->media_file,
                        'filename' => $this->file_name,
                    ],
                ]
        ])->getBody()
          ->getContents();
    }

    public function get(string $id): ?object
    {
        return Http::withHeaders($this->service->getHeaders())
            ->get($this->service::$baseUrl.'/v1/media/'.$id)
            ->object();
    }
}
