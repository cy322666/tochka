<?php

namespace App\Jobs\Api\SalesBot;

use App\Models\Api\SalesBot\FilterContecst;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class GetHook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public FilterContecst $hook)
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
        Artisan::call('salesbot:get-hook-filter-contecst', ['hook' => $this->hook]);
    }
}
