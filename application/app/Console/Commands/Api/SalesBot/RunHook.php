<?php

namespace App\Console\Commands\Api\SalesBot;

use App\Models\Account;
use App\Models\Api\SalesBot\FilterContecst;
use App\Services\amoCRM\Client;
use App\Services\SaleBot;
use Illuminate\Console\Command;

class RunHook extends Command
{
    protected $signature = 'salesbot:run-hook-filter-contecst {hook}';

    protected $description = 'Проверка и отписка в списке Salebot ...';

    private array $statuses;

    private static array $pipelines = [
        3738346, //op
        6552278, //kval
        5674156, //dop
        7566598, //prob
    ];

    //list_id

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $amoApi = (new Client(Account::query()->first()))->init();

        $hook = $this->argument('hook');

        $saleLead = $amoApi->service->ajax()->get('/api/v4/leads/16727041');

        if (in_array($saleLead->pipeline_id, static::$pipelines)) {

            $response = (new SaleBot())->unsubscribe();

            dd($response);

            $hook->in_sales = true;
            //отписываем
        }

        $hook->status = 1;
        $hook->save();
    }
}
