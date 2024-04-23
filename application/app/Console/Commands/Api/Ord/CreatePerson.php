<?php

namespace App\Console\Commands\Api\Ord;

use App\Models\Account;
use App\Models\Api\Ord\Person;
use App\Models\Api\Ord\Transaction;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Notes;
use App\Services\Ord\OrdService;
use Illuminate\Console\Command;
use Ramsey\Uuid\Uuid;

class CreatePerson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ord:create-person {transaction}';

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
        $transaction = Transaction::query()->find($this->argument('transaction'));

        $amoApi = (new Client(
            Account::query()
                ->where('subdomain', 'tochkaznanij')
                ->first()
        ))->init();

        $ordApi = new OrdService(env('APP_ENV'));
        $lead   = $amoApi->service->leads()->find($transaction->lead_id);
        $person = $ordApi->person();

        $company = $lead->company;

        $searchPerson = Person::query()
            ->where('inn', $company->cf('ИНН')->getValue())
            ->first();

        if (!$searchPerson) {

            $person->uuid  = Uuid::uuid4();
            $person->name  = $company->cf('ФИО')->getValue();
            $person->type  = Person::matchType($company->cf('Тип')->getValue());
            $person->inn   = $company->cf('ИНН')->getValue();
            $person->role  = 'publisher';
            $result = $person->create();

            if (empty($result->error)) {

                $transaction->person_uuid = $person->uuid;
                $transaction->save();

                Notes::addOne($lead, 'Успешное создание контрагента : '.$transaction->person_uuid);
            } else
                Notes::addOne($lead, 'Произошла ошибка при синхронизации контрагента : '.json_encode($result->error));
        } else {

            $transaction->person_uuid = $searchPerson->uuid;

            Notes::addOne($lead, 'Успешная синхронизация существующего контрагента в ОРД : '.$transaction->person_uuid);
        }

        $ordApi->person()->get($searchPerson ? $searchPerson->uuid : $person->uuid);

        $transaction->company_id = $company->id;
        $transaction->contact_id = $lead->contact->id;
        $transaction->save();
    }
}
