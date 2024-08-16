<?php

namespace App\Console\Commands\Api\Ord;

use App\Models\Account;
use App\Models\Api\Ord\Contract;
use App\Models\Api\Ord\Person;
use App\Models\Api\Ord\Transaction;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Notes;
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
        //ищем базовый
        //если нет создаем
        //если есть то крепим
        $transaction = Transaction::query()->find($this->argument('transaction'));

        $ordApi = new OrdService(env('APP_ENV'));
        $amoApi = (new Client(
            Account::query()
                ->where('subdomain', 'tochkaznanij')
                ->first()
        ))->init();

        $lead = $amoApi->service->leads()->find($transaction->lead_id);

        $searchBaseContract = Contract::query()
            ->where('serial', $lead->company->cf('Номер договора')->getValue())
            ->where('type', 'service')
            ->first();

        if (!$searchBaseContract) {
            //создаем основной
            $contract = $ordApi->contract();

            $contract->uuid = Uuid::uuid4();
            $contract->type = 'service';
            $contract->client_external_id = $transaction->person_uuid;
            $contract->contractor_external_id = 'my';
            $contract->date = Carbon::parse($lead->cf('Дата договора')->getValue())->format('Y-m-d');
            $contract->serial = $lead->cf('Номер заявки')->getValue();
            $contract->subject_type = 'distribution';
            $result = $contract->create();

            if (!empty($result->error)) {

                Notes::addOne($lead, 'Произошла ошибка при создании договора : '.json_encode($result->error));

                exit;
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

            Notes::addOne($lead, implode("\n", [
                ' Успешное создание договора : '.$searchBaseContract->uuid.' , '.$searchBaseContract->serial,
            ]));
        }

        $contract = Contract::query()
            ->where('serial', $lead->cf('Номер заявки')->getValue())
            ->where('type', '!=', 'service')
            ->first();

        if ($contract) {

            $transaction->contract_uuid = $contract->uuid;
            $transaction->contract_serial = $contract->serial;
            $transaction->parent_contract_external_id = $searchBaseContract->uuid;
            $transaction->save();

            Notes::addOne($lead, implode("\n", [
                ' Успешная синхронизация заявки : ',
                ' Договор : '.$searchBaseContract->uuid.' , '.$searchBaseContract->serial,
                ' Заявка : '.$contract->uuid.' , '.$contract->serial,
            ]));

        } else {

            //для новой заявки
            $contract = $ordApi->contract();

            $contract->uuid = Uuid::uuid4();
            $contract->type = 'additional';
            $contract->client_external_id = $searchBaseContract->client_external_id;
            $contract->contractor_external_id = $searchBaseContract->contractor_external_id;
            $contract->date = Carbon::parse($lead->cf('Дата заявки')->getValue())->format('Y-m-d');
            $contract->serial = $lead->cf('Номер заявки')->getValue();
            $contract->subject_type = 'distribution';
            $contract->parent_contract_external_id = $searchBaseContract->uuid;

            $result = $contract->create();

            if (empty($result->error)) {

                $transaction->contract_uuid = $contract->uuid;
                $transaction->contract_serial = $contract->serial;
                $transaction->parent_contract_external_id = $searchBaseContract->uuid;
                $transaction->save();

                Notes::addOne($lead, implode("\n", [
                    ' Успешное создание заявки : ',
                    ' Договор : '.$searchBaseContract->uuid.' , '.$searchBaseContract->serial,
                    ' Заявка : '.$contract->uuid.' , '.$contract->serial,
                ]));
            } else
                Notes::addOne($lead, 'Произошла ошибка при создании заявки : '.json_encode($result->error));
        }
    }
}
