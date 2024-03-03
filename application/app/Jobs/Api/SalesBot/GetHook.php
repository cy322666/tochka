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

    public function __construct(public Request $request)
    {
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
            'list_id' => $this->request->list,
            'lead_id' => $this->amo_lead_id,
            'client_id'  => $this->client_id,
            'contact_id' => $this->amo_client_id,
        ]);

        Artisan::call('salesbot:run-hook-filter-contecst', ['hook' => $hook]);
    }
}
