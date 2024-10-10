<?php

namespace App\Services\Ord;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class Invoice
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

    public $creative_external_id;
    public $pad_external_id;
    public $date_start_planned;
    public $date_end_planned;
    public $date_start_actual;
    public $date_end_actual;
    public $amount_per_event;
    public $invoice_shows_count;
    public $shows_count;

    public function __construct(public OrdService $service) {}

    public function create(): ?object
    {
        $body = [
            "contract_external_id" => $this->contract_external_id,
            "date" => $this->date,
            "serial" => $this->serial,
            "date_start" => $this->date_start,
            "date_end" => $this->date_end,
            "amount" => $this->amount,
            "client_role" => $this->client_role,
            "contractor_role" => $this->contractor_role,
            "flags" => ["vat_included"],
            "items" => [[
                "contract_external_id" => $this->contract_external_id,
                "amount" => $this->amount,
                "flags" => ["vat_included"],
                "creatives" => [
                    [
                        "creative_external_id" => $this->creative_external_id,
                        "platforms" => [[
                            "pad_external_id"  => $this->pad_external_id,
                            "amount_per_event" => (string)$this->amount_per_event,
                            "invoice_shows_count" => (float)$this->invoice_shows_count,
                            "shows_count" => (int)$this->shows_count,
                            "amount" => $this->amount,
                            "date_start_planned" => $this->date_start_planned,
                            "date_end_planned"   => $this->date_end_planned,
                            "date_start_actual"  => $this->date_start_actual,
                            "date_end_actual" => $this->date_end_actual,
                            "pay_type" => "cpm",
                            "flags" => ["vat_included"],
                        ]]
                        ]
                    ]
                ]
            ]
        ];

        Log::debug(__METHOD__, $body);

        return Http::withHeaders($this->service->getHeaders())
            ->put($this->service::$baseUrl . '/v1/invoice/' . $this->uuid, $body)->object();
    }

    public function get(string $id)
    {
        $response = Http::withHeaders($this->service->getHeaders())
            ->get($this->service::$baseUrl . '/v1/invoice/' . $id);

        return $this->service->parseResponse($response);
    }

    public function list(): array
    {
        for ($offset = 0, $limit = 1, $persons = []; $offset < 3; $offset += $limit) {
            $response = Http::withHeaders($this->service->getHeaders())
                ->get($this->service::$baseUrl . '/v1/invoice', [
                    'limit' => $limit,
                    'offset' => $offset,
                ]);

            $persons = array_merge(
                (array)($this->service->parseResponse($response))->external_ids,
                $persons
            );
        }

        return $persons;
    }

    public function add(): ?object
    {
        return Http::withHeaders($this->service->getHeaders())
            ->patch($this->service::$baseUrl . '/v2/invoice/' . $this->uuid . '/items', [
                "items" => [[
                    "contract_external_id" => $this->contract_external_id,
                    "amount" => $this->amount,
                    "flags" => ["vat_included"],
                    "creatives" => [
                        [
                            "creative_external_id" => $this->creative_external_id,
                            "platforms" => [[
                                    "pad_external_id"  => $this->pad_external_id,
                                    "amount_per_event" => (string)$this->amount_per_event,
                                    "invoice_shows_count" => (float)$this->invoice_shows_count,
                                    "shows_count" => (int)$this->shows_count,
                                    "amount" => $this->amount,
                                    "date_start_planned" => $this->date_start_planned,
                                    "date_end_planned"   => $this->date_end_planned,
                                    "date_start_actual"  => $this->date_start_actual,
                                    "date_end_actual" => $this->date_end_actual,
                                    "pay_type" => "cpm"
                                ]]
                            ]
                        ]
                    ]
                ]
            ])->object();
    }
}
