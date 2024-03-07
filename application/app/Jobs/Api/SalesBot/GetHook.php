<?php

namespace App\Jobs\Api\SalesBot;

use App\Models\Api\SalesBot\FilterContecst;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class GetHook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 10;

    public array $request;

    public function __construct(array $request)
    {
        $this->request = $request;
        $this->onQueue('salesbot-filter');
    }

    public function tags(): array
    {
        return ['salesbot', 'filter-contects'];
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $hook = FilterContecst::query()->create([
            'list_id' => $this->request['list'],
            'lead_id' => $this->request['amo_lead_id'] ?? null,
            'client_id'  => $this->request['client_id'],
            'contact_id' => $this->request['amo_client_id'] ?? null,
        ]);

        Artisan::call('salesbot:run-hook-filter-contecst', ['hook' => $hook]);
    }
}
