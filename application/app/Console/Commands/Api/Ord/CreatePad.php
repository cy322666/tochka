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

    const TG_PIPELINE_ID = 7246822;
    const IG_PIPELINE_ID = 6964646;

    /**
     * Execute the console command.
     * @throws \Exception
     */
    public function handle()
    {
        $transaction = Transaction::query()->find($this->argument('transaction'));

        $ordApi = new OrdService(env('APP_ENV'));
        $amoApi = (new Client(
            Account::query()
                ->where('subdomain', 'tochkaznanij')
                ->first()
        ))->init();

        $lead = $amoApi->service->leads()->find($transaction->lead_id);
        $contact = $lead->contact;

        sleep(3);

        if (!$transaction->person_uuid) {

            Notes::addOne($lead,'При создании площадки нет контрагента в БД');

            return false;
        }

        $searchPerson = Person::query()
            ->where('uuid', $transaction->person_uuid)
            ->first();

        if (!$searchPerson) {

            sleep(10);

            $searchPerson = Person::query()
                ->where('uuid', $transaction->person_uuid)
                ->first();

            if (!$searchPerson) {

                Notes::addOne($lead,'При создании площадки не нашли контрагента по uuid : '.$transaction->person_uuid);

                return false;
            }

        }

        $name = $lead->pipeline_id == self::IG_PIPELINE_ID ? $contact->cf('Ник блогера')->getValue() : $contact->cf('Название канала')->getValue();

        $searchPad = Pad::query()
            ->where('person_external_id', $searchPerson->uuid)
            ->where('name', $name)
            ->first();

        if (!$searchPad) {

            $pad = $ordApi->pad();
            $pad->uuid = Uuid::uuid4();
            $pad->person_external_id = $transaction->person_uuid;
            $pad->is_owner = true;
            $pad->type = 'web';
            $pad->name = $name;
            $pad->url = $contact->cf('Ссылка на канал')->getValue() ?: 'https://google.com';
            $result = $pad->create();

            $padNew = $ordApi->pad()->get($pad->uuid);

            if ($padNew && (!$result || empty($result->error))) {

                $transaction->pad_uuid = $pad->uuid;
                $transaction->save();

                Notes::addOne($lead,'Успешное создание площадки : '.$pad->uuid);

                $lead->cf('ОРД Площадка')->setValue($result ? json_encode($result, JSON_UNESCAPED_UNICODE) : null);
                $lead->save();

                return true;

            } else {

                Notes::addOne($lead, 'Произошла ошибка при создании площадки : '.json_encode($result->error, JSON_UNESCAPED_UNICODE));

                return false;
            }
        } else {

            $transaction->pad_uuid = $searchPad->uuid;
            $transaction->save();

            Notes::addOne($lead, 'Успешная синхронизация существующей площадки : '.$transaction->pad_uuid);

            $result = $ordApi->pad()->get($searchPad->uuid);

            $lead->cf('ОРД Площадка')->setValue(json_encode($result, JSON_UNESCAPED_UNICODE));
            $lead->save();

            return true;
        }
    }
}
