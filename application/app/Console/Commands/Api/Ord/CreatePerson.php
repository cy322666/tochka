<?php

namespace App\Console\Commands\Api\Ord;

use App\Models\Account;
use App\Models\Api\Ord\Person;
use App\Services\amoCRM\Client;
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
        $transaction = $this->argument('transaction');

        $amoApi = (new Client(
            Account::query()
                ->where('subdomain', 'tochkaznanij')
                ->first()
        ))->init();

        $ordApi = new OrdService();

        $lead   = $amoApi->service->leads()->find($transaction->lead_id);
        $person = $ordApi->person();

        $company = $lead->company;

        $searchPerson = Person::query()
            ->where('inn', $company->cf('ИНН')->getValue())
            ->first();

        if (!$searchPerson) {

            $person->uuid  = Uuid::uuid4();
            $person->name  = $company->name;
            $person->type  = Person::matchType($company->cf('Тип')->getValue());
            $person->inn   = $company->cf('ИНН')->getValue();
            $person->phone = $company->cf('Телефон')->getValue();
            $person->create();

            $transaction->uuid_person = $person->uuid;
            $transaction->phone = $person->phone;
            $transaction->name = $person->name;
            $transaction->type = $person->type;
            $transaction->inn  = $person->inn;
            $transaction->save();
        } else
            $transaction->person_uuid = $searchPerson->uuid;

        $ordApi->person()->get($searchPerson ? $searchPerson->uuid : $person->uuid);

        $transaction->company_id = $company->id;
        $transaction->contact_id = $lead->contact->id;
        $transaction->save();

//        $person->rs_url;
    }
}
