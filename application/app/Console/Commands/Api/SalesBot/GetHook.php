<?php

namespace App\Console\Commands\Api\SalesBot;

use App\Models\Account;
use App\Models\Api\SalesBot\FilterContecst;
use App\Services\amoCRM\Client;
use Illuminate\Console\Command;

class GetHook extends Command
{
    protected $signature = 'salesbot:get-hook-filter-contecst {hook}';

    protected $description = 'Получение из Salesbot ...';

    private Client $amoApi;
    private array $statuses;
    private array $pipelines;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->amoApi = (new Client(Account::query()->first()))->init();

        $this->statuses  = [];
        $this->pipelines = [];
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $hook = $this->argument('hook');

        $contact = $this->amoApi->service->contacts()->find($hook->contact_id);

        //ищем открытые сделки - одним запросом?
        if($contact->leads) {

            foreach ($contact->leads as $lead) {
                //ищем в нужных этапах
                foreach ($this->statuses as $status_id) {

                    if ($lead->status_id == $status_id) {

                        //если есть в оп то бракуем
                    }
                }
            }
        }
        //отправка команды на удаление
    }
}
