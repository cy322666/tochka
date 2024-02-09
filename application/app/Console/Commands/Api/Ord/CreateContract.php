<?php

namespace App\Console\Commands\Api\Ord;

use App\Models\Account;
use App\Services\amoCRM\Client;
use App\Services\Ord\OrdService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Ramsey\Uuid\Uuid;

class CreateContract extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ord:create-contract {transaction}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     * @throws \Exception
     */
    public function handle()
    {
        $transaction = $this->argument('transaction');

        $amoApi = (new Client(Account::query()->first()))->init();
        $ordApi = new OrdService();

        $lead = $amoApi->service->leads()->find(44622459);//$transaction->lead_id);

        $contract = $ordApi->contract();

        /*
  +"create_date": "2024-02-09T17:30:02Z"
  +"type": "additional"
  +"client_external_id": "22ee5504-7026-405e-93cf-39cb09ca722b"
  +"contractor_external_id": "8yjfa2yw4zm-arzxqw9ww"
  +"subject_type": "distribution"
  +"date": "2023-02-15"
  +"serial": "312312312"
  +"flags": []
  +"parent_contract_external_id": "620313dc-090e-42dd-b0cf-00b9ef61aae8"

         */
        $contract->uuid = Uuid::uuid4();
        $contract->type = 'additional';//$lead->cf('')->getValue(); //доп соглашение
        $contract->client_external_id = '22ee5504-7026-405e-93cf-39cb09ca722b';//$transaction->uuid_person;
        $contract->contractor_external_id = '8yjfa2yw4zm-arzxqw9ww';//TODO точка
        $contract->date = Carbon::parse($lead->cf('Дата договора')->getValue())->format('Y-m-d');
        $contract->serial = $lead->cf('Номер заявки')->getValue();
//        $contract->action_type = 'distribution';
        $contract->subject_type = 'distribution'; //распростр рекламы
        $contract->parent_contract_external_id = '620313dc-090e-42dd-b0cf-00b9ef61aae8';//$lead->cf('')->getValue();
//        $contract->amount  = $lead->cf('')->getValue();//"500.5"
        $result = $contract->create();

        if (empty($result->error)) {

            $transaction->contract_uuid = $contract->uuid;
            $transaction->contract_date = $contract->date;
            $transaction->contract_serial = $contract->serial;
            $transaction->parent_contract_external_id = $contract->parent_contract_external_id;
            $transaction->save();
        }
    }
}
