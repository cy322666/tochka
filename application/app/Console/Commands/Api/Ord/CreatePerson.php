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

            $personNew = $ordApi->person()->get($person->uuid);

            if ($personNew && empty($result->error)) {

                $transaction->person_uuid = $person->uuid;
                $transaction->save();

                Notes::addOne($lead, 'Успешное создание контрагента : '.$transaction->person_uuid);

                $lead->cf('ОРД Контрагент')->setValue(json_encode($result, JSON_UNESCAPED_UNICODE));
                $lead->save();

                $transaction->company_id = $company->id;
                $transaction->contact_id = $lead->contact->id;
                $transaction->save();

                Person::query()->create([
                    'uuid' => $person->uuid,
                    'juridical_details' => json_encode($personNew->juridical_details) ?: null,
                    'create_date' => $personNew->create_date,
                    'name'  => !empty($personNew->name) ? $personNew->name : null,
                    'roles' => json_encode($personNew->roles) ?: null,
                    'type'  => !empty($personNew->juridical_details->type) ? $personNew->juridical_details->type : null,
                    'phone' => !empty($personNew->juridical_details->phone) ? $personNew->juridical_details->phone : null,
                    'inn'   => !empty($personNew->juridical_details->inn) ? $personNew->juridical_details->inn : null,
//                          'rs_url' => $detail->juridical_details->rs_url ?? null,
                ]);

                return true;

            } else {

                Notes::addOne($lead, 'Произошла ошибка при синхронизации контрагента : '.json_encode($result->error, JSON_UNESCAPED_UNICODE));

                return false;
            }
        } else {

            $transaction->company_id = $company->id;
            $transaction->contact_id = $lead->contact->id;
            $transaction->person_uuid = $searchPerson->uuid;
            $transaction->save();

            Notes::addOne($lead, 'Успешная синхронизация существующего контрагента в ОРД : '.$transaction->person_uuid);

            $result = $ordApi->person()->get($transaction->person_uuid);

            $lead->cf('ОРД Контрагент')->setValue(json_encode($result, JSON_UNESCAPED_UNICODE));
            $lead->save();

            return true;
        }
    }
}
