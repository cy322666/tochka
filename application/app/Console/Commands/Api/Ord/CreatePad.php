<?php

namespace App\Console\Commands\Api\Ord;

use App\Models\Account;
use App\Models\Api\Ord\Contract;
use App\Models\Api\Ord\Pad;
use App\Models\Api\Ord\Person;
use App\Models\Api\Ord\Transaction;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Notes;
use App\Services\Ord\OrdService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class CreatePad extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ord:create-pad {transaction}';

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

        $ordApi = new OrdService();
        $amoApi = (new Client(
            Account::query()
                ->where('subdomain', 'tochkaznanij')
                ->first()
        ))->init();

        $lead = $amoApi->service->leads()->find($transaction->lead_id);
        $pad  = $ordApi->pad();

        $searchPerson = Person::query()
            ->where('uuid', $transaction->person_uuid)
            ->first();

        $searchPad = Pad::query()
            ->where('person_external_id', $searchPerson->uuid)
            ->first();

        if (!$searchPad) {

            $pad->uuid = Uuid::uuid4();
            //$pad->create_date = Carbon::now()->timezone('Europe/Moscow')->format('Y-m-d H:i:s');
            $pad->person_external_id = $transaction->person_uuid;
            $pad->is_owner = false;
            $pad->type = 'web';
            $pad->name = $lead->cf('Ник блогера')->getValue();
            $pad->url = $lead->cf('Ссылка для площадки')->getValue();
            $pad->create();

            if (empty($result->error)) {

                $transaction->pad_uuid = $pad->uuid;

                Notes::addOne($lead, implode("\n", ' Успешное создание площадки : '.$pad->uuid));
            } else
                Notes::addOne($lead, 'Произошла ошибка при создании площадки : '.json_encode($result->error));
        } else {

            $transaction->pad_uuid = $searchPad->uuid;

            Notes::addOne($lead, implode("\n", ' Успешная синхронизация существующей площадки : '.$pad->uuid));
        }

        $transaction->save();
    }
}
