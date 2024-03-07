<?php

namespace App\Console\Commands\Api\SalesBot;

use App\Models\Account;
use App\Models\Api\SalesBot\FilterContecst;
use App\Services\amoCRM\Client;
use App\Services\SaleBot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RunHook extends Command
{
    protected $signature = 'salesbot:run-hook-filter-contecst {hook}}';

    protected $description = 'Проверка и отписка в списке Salebot ...';

    private array $statuses;

    private static array $pipelines = [
        3738346, //op
        6552278, //kval
        5674156, //dop
        7566598, //prob
    ];

    /**
     * @throws \Exception
     */
    public function handle()
    {
        try {
            $hook = $this->argument('hook');

            $amoApi = (new Client(Account::query()->find(6)))->init();

            $saleLead = $amoApi->service->ajax()->get('/api/v4/leads/'.$hook->lead_id);

            if (in_array($saleLead->pipeline_id, static::$pipelines) &&
                $saleLead->status_id != 142 &&
                $saleLead->status_id != 143) {

                (new SaleBot(env('SALEBOT_TOKEN')))->unsubscribe($hook->list_id, $hook->client_id);

                $hook->in_sales = true;
            }

            $hook->pipeline_id = $saleLead->pipeline_id;
            $hook->status = 1;
            $hook->save();

        } catch (\Throwable $e) {

            Log::error(__METHOD__.' : '.$e->getMessage().' '.$e->getFile().' '.$e->getLine());

            $hook->status = 3;
            $hook->save();
        }
    }
}
