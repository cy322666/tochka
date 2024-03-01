<?php

namespace App\Console\Commands\Api\Ord;

use App\Models\Account;
use App\Models\Api\Ord\Contract;
use App\Models\Api\Ord\Person;
use App\Models\Api\Ord\Transaction;
use App\Services\amoCRM\Client;
use App\Services\Ord\OrdService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
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
        //TODO создаем и базовый если его нет
        //ищем базовый
        //если нет создаем
        //если есть то крепим
        $transaction = Transaction::query()->find($this->argument('transaction'));

        $amoApi = (new Client(Account::query()->first()))->init();
        $ordApi = new OrdService();

        $lead = $amoApi->service->leads()->find($transaction->lead_id);

        $searchPerson = Person::query()
            ->where('uuid', $transaction->person_uuid)
            ->first();

        $searchBaseContract = Contract::query()
            ->where('client_external_id', $searchPerson->uuid)//TODO?
            ->where('type', 'service')
            ->first();

        if (!$searchBaseContract) {
            //создаем основной
            $contract = $ordApi->contract();

            $contract->uuid = Uuid::uuid4();
            $contract->type = 'service';
            $contract->client_external_id = $transaction->person_uuid;
            $contract->contractor_external_id = '8yjfa2yw4zm-arzxqw9ww';//TODO точка uuid
            $contract->date = Carbon::parse($lead->cf('Дата договора')->getValue())->format('Y-m-d');
            $contract->serial = $lead->cf('Номер договора')->getValue();
            $contract->subject_type = 'distribution';
//            $contract->parent_contract_external_id = $baseContract->uuid;
//            $contract->amount  = $lead->sale;
            $result = $contract->create();

            if (!empty($result->error)) {

                dd(__METHOD__.' : '.$result->error);
            }

            $searchBaseContract = Contract::query()
                ->create([
                    'uuid' => $contract->uuid,
                    'type' => $contract->type,
                    'client_external_id' => $contract->client_external_id,
                    'contractor_external_id' => $contract->contractor_external_id,
                    'date' => $contract->date,
                    'serial' => $contract->serial,
                    'subject_type' => $contract->subject_type,
                ]);
        }

        //для заявки
        $contract = $ordApi->contract();

        $contract->uuid = Uuid::uuid4();
        $contract->type = 'additional';
        $contract->client_external_id = $transaction->person_uuid;
        $contract->contractor_external_id = '8yjfa2yw4zm-arzxqw9ww';//TODO точка uuid
        $contract->date = Carbon::parse($lead->cf('Дата договора')->getValue())->format('Y-m-d');
        $contract->serial = Contract::getSerialName($lead->cf('Номер договора')->getValue());
        $contract->subject_type = 'distribution';
        $contract->parent_contract_external_id = $searchBaseContract->uuid;
//        $contract->amount  = $lead->sale;

        $result = $contract->create();

        if (empty($result->error)) {

            $transaction->contract_uuid = $contract->uuid;
    //            $transaction->contract_date = $contract->date;
            $transaction->contract_serial = $contract->serial;
            $transaction->parent_contract_external_id = $searchBaseContract->uuid;
            $transaction->save();
        } else
            dd(__METHOD__.' : '.$result->error);

//        $lead->cf('Номер заявки')->setValue($serial);
//        $lead->save();
    }
}
