<?php

namespace App\Console\Commands\Api\Ord;

use App\Models\Account;
use App\Models\Api\Ord\Contract;
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
        $transaction = $this->argument('transaction');

        $amoApi = (new Client(Account::query()->first()))->init();
        $ordApi = new OrdService();

        $lead = $amoApi->service->leads()->find($transaction->lead_id);

        $serial = Contract::getSerialName($lead->cf('Номер договора')->getValue());

        $contract = $ordApi->contract();

        $baseContract = Contract::query()
            ->where('serial', $lead->cf('Номер договора')->getValue())
            ->first();

        $contract->uuid = Uuid::uuid4();
        $contract->type = 'additional';
        $contract->client_external_id = $transaction->person_uuid;
        $contract->contractor_external_id = '8yjfa2yw4zm-arzxqw9ww';//TODO точка uuid
        $contract->date = Carbon::parse($lead->cf('Дата договора')->getValue())->format('Y-m-d');
        $contract->serial = $serial;
        $contract->subject_type = 'distribution';
        $contract->parent_contract_external_id = $baseContract->uuid;
        $contract->amount  = $lead->sale;

        $result = $contract->create();

        Log::alert(__METHOD__, [$result]);
//        if (empty($result->error)) {

            $transaction->contract_uuid = $contract->uuid;
//            $transaction->contract_date = $contract->date;
            $transaction->contract_serial = $contract->serial;
            $transaction->parent_contract_external_id = $baseContract->uuid;
            $transaction->save();
//        }

//        $lead->cf('Номер заявки')->setValue($serial);
//        $lead->save();
    }
}
