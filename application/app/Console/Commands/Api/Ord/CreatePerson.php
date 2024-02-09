<?php

namespace App\Console\Commands\Api\Ord;

use App\Models\Account;
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

        $amoApi = (new Client(Account::query()->first()))->init();
        $ordApi = new OrdService();

        $lead   = $amoApi->service->leads()->find($transaction->lead_id);
        $person = $ordApi->person();

        $company = $lead->company;

        $person->uuid  = Uuid::uuid4();
        $person->name  = $company->name;
        $person->type  = 'physical';
        $person->inn   = $company->cf('ИНН')->getValue();
        $person->phone = $company->cf('Телефон')->getValue();
        $person->create();

        $transaction->uuid_person = $person->uuid;
        $transaction->name = $person->name;
        $transaction->type = $person->type;
        $transaction->inn = $person->inn;
        $transaction->phone = $person->phone;
        $transaction->save();

        $personCreated = $ordApi->person()->get($person->uuid);

        $transaction->company_id = $company->id;
        $transaction->contact_id = $lead->contact->id;
        $transaction->status = is_array($personCreated);
        $transaction->save();

//        $person->rs_url;
    }
}
