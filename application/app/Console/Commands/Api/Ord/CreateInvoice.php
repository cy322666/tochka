<?php

namespace App\Console\Commands\Api\Ord;

use App\Models\Account;
use App\Models\Api\Ord\Contract;
use App\Models\Api\Ord\Creative;
use App\Models\Api\Ord\Pad;
use App\Models\Api\Ord\Person;
use App\Models\Api\Ord\Transaction;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Notes;
use App\Services\Ord\OrdService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class CreateInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ord:create-invoice {lead_id} {transaction?}';

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
        $transaction = $this->argument('transaction') ? Transaction::query()->find($this->argument('transaction')) : null;

        $ordApi = new OrdService(env('APP_ENV'));
        $amoApi = (new Client(
            Account::query()
                ->where('subdomain', 'tochkaznanij')
                ->first()
        ))->init();

        $lead = $amoApi->service->leads()->find($this->argument('lead_id'));

        $name = $lead->pipeline_id == CreatePad::IG_PIPELINE_ID ? $lead->contact->cf('Ник блогера')->getValue() : $lead->contact->cf('Название канала')->getValue();

        if (!$transaction) {

            $transaction = Transaction::query()
                ->create([
                    'lead_id' => $lead->id,
                    'contact_id' => $lead->contact->id,
                    'company_id' => $lead->company->id,
                    'contract_serial' => $lead->cf('Номер заявки')->getValue(),
                ]);

            $searchPerson = Person::query()
                ->where('inn', $lead->company->cf('ИНН')->getValue())
                ->first();

            Log::debug(__METHOD__.' : search person', $searchPerson ?: $searchPerson->toArray());

            $contract = Contract::query()
                ->where('serial', $lead->cf('Номер заявки')->getValue())
                ->where('type', '!=', 'service')
                ->latest('created_at')
                ->first();

            Log::debug(__METHOD__.' : search contract', $contract ?: $contract->toArray());

            $searchPad = Pad::query()
                ->where('person_external_id', $searchPerson->uuid)
                ->where('name', $name)
                ->first();

            Log::debug(__METHOD__.' : search pad', $searchPad ?: $searchPad->toArray());

            $transaction->person_uuid = $searchPerson?->uuid;
            $transaction->contract_uuid = $contract?->uuid;
            $transaction->save();

            if (!$searchPad) {

                Notes::addOne($lead, 'Ошибка: Заведен вручную, у контрагента не найдена площадка'.PHP_EOL.' Название : '.$name.PHP_EOL.' Контрагент : '.$searchPerson->uuid);

                $result3 = Artisan::call('ord:create-pad', ['transaction' => $transaction->id]);

                if (!$result3) {

                    Notes::addOne($lead, 'Ошибка: ответ от команды создания площадки отрицательный');

                    return false;
                } else {

                    Artisan::call('ord:get-pads');

                    $searchPad = Pad::query()
                        ->where('person_external_id', $searchPerson->uuid)
                        ->where('name', $name)
                        ->first();
                }
            }

            $creative = Creative::query()
                ->where('contract_external_id', $contract->uuid)
                ->first();

            Log::debug(__METHOD__.' : search creative', $creative ?: $creative->toArray());

            if (!$creative) {

                if ($transaction->erid) {

                    $creative = Creative::query()
                        ->where('erid', $transaction->erid)
                        ->first();
                }
            }

            $transaction->pad_uuid = $searchPad?->uuid;
            $transaction->creative_uuid = $creative?->uuid;
            $transaction->save();

            $personUuid = $searchPerson->uuid;
            $padUuid  = $searchPad->uuid;
            $contractUuid   = $contract->uuid;
            $creativeUuid = $creative->uuid;

        } else {

            $searchPerson = Person::query()
                ->where('uuid', $transaction->person_uuid)
                ->first();

            $searchPad = Pad::query()
                ->where('person_external_id', $searchPerson->uuid)
                ->where('name', $name)
                ->first();

            if (!$searchPad) {

                Notes::addOne($lead, 'Ошибка: Заведен вручную, у контрагента не найдена площадка'.PHP_EOL.' Название : '.$name.PHP_EOL.' Контрагент : '.$searchPerson->uuid);

                $result3 = Artisan::call('ord:create-pad', ['transaction' => $transaction->id]);

                if (!$result3)

                    return false;
            }

            $transaction->refresh();

            if (!$transaction->creative_uuid) {

                if (!$transaction->erid) {

                    Notes::addOne($lead, 'Ошибка: Не указан uuid креатива');

                    return false;
                } else {

                    $creative = Creative::query()
                        ->where('erid', $transaction->erid)
                        ->first();

                    if (!$creative) {

                        $creative = Creative::query()
                            ->where('contract_external_id', $contractUuid)
                            ->first();

                        if (!$creative) {

                            Notes::addOne($lead, 'Ошибка: Не указан uuid креатива и не найден по erid');

                            return false;
                        } else {

//                            $transaction->contact_uuid = $contractUuid;
                            $transaction->creative_uuid = $creative->uuid;
                            $transaction->erid = $creative->erid;
                            $transaction->save();
                        }
                    }
                }

                $creativeUuid = $creative->uuid;
            } else
                $creativeUuid   = $transaction->creative_uuid;

            $contractUuid   = $transaction->contract_uuid;
            $padUuid = $transaction->pad_uuid;
        }

        $pad = $ordApi->pad()->get($transaction->pad_uuid);

        if (!$pad) {

            Notes::addOne($lead, 'Ошибка: по uuid не нашли площадку в ОРД : '.$padUuid);

            $result = Artisan::call('ord:create-pad', ['transaction' => $transaction->id]);

            if (!$result) {

                return false;
            }

            $transaction->refresh();

            $padUuid = $transaction->pad_uuid;

            if (!$padUuid) {

                Notes::addOne($lead, 'Ошибка: после команды создания площадки не получен uuid');

                return;
            }
        }

        $invoice = $ordApi->invoice();

        $date = Carbon::parse($lead->cf('Дата рекламы план')->getValue())->format('Y-m-d');
        $dateExpose = Carbon::parse($lead->cf('Дата выставления (акта)')->getValue())->format('Y-m-d');
        $dateStart = Carbon::parse($lead->cf('Дата рекламы факт')->getValue())->format('Y-m-d');
        $dateEnd = Carbon::parse($lead->cf('Дата окончания факт')->getValue())->format('Y-m-d');
        $dateEndPlan = Carbon::parse($lead->cf('Дата окончания план')->getValue())->format('Y-m-d');

        $invoice->uuid = Uuid::uuid4();
        $invoice->contract_external_id = $contractUuid;
        $invoice->date = $dateExpose;
        $invoice->date_start = $dateStart;
        $invoice->date_end = $dateEnd;
        $invoice->amount = $lead->sale;
        $invoice->client_role = 'advertiser';
        $invoice->contractor_role = 'publisher';
        $invoice->serial = $lead->cf('Номер заявки')->getValue();

        $invoice->creative_external_id = $creativeUuid;
        $invoice->pad_external_id = $padUuid;
        $invoice->date_start_planned = $dateStart;
        $invoice->date_end_planned = $dateEndPlan;
        $invoice->date_start_actual = $dateStart;
        $invoice->date_end_actual = $dateEnd;
        $invoice->invoice_shows_count = $lead->cf('Количество показов')->getValue() * 1000;

        $invoice->pad_external_id = $padUuid;
        $invoice->creative_external_id = $creativeUuid;
        $invoice->date_start_planned = $date;
        $invoice->date_end_planned  = $dateEndPlan;
        $invoice->date_start_actual = $dateStart;
        $invoice->date_end_actual   = $dateEnd;
        $invoice->invoice_shows_count = $lead->cf('Количество показов')->getValue();
        $invoice->shows_count = $lead->cf('Количество показов')->getValue();
        $invoice->amount = $lead->sale;;
        $invoice->amount_per_event =  round($invoice->amount / $invoice->shows_count, 3);

        $result = $invoice->create();

        try {
            $lead->cf('ОРД Акт')->setValue(json_encode($result, JSON_UNESCAPED_UNICODE));
            $lead->save();
        } catch (\Throwable $e) {}

//        $result = $invoice->add();

        if (empty($result->error)) {

            $transaction->invoice_uuid = $invoice->uuid;
            $transaction->save();

            Notes::addOne($lead, 'Успешное создание акта : '.$transaction->invoice_uuid);

        } else {

            Notes::addOne($lead, 'Произошла ошибка при создании акта');

            Log::error(__METHOD__, [$result ? $result->error : 'Неизвестная ошибка']);
        }
    }
}
