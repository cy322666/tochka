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
use Ramsey\Uuid\Uuid;

class CreateInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ord:create-invoice {transaction?} {lead_id}';

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

        if (!$transaction) {

            $searchPerson = Person::query()
                ->where('inn', $company->cf('ИНН')->getValue())
                ->first();

            $contract = Contract::query()
                ->where('serial', $lead->cf('Номер заявки')->getValue())
                ->where('type', '!=', 'service')
                ->latest('created_at')
                ->first();

            $searchPad = Pad::query()
                ->where('person_external_id', $searchPerson->uuid)
                ->where('name', $name)
                ->first();

            $creative = Creative::query()
                ->where('contract_external_id', $contract->uuid)
                ->first();

            $personUuid = $searchPerson->uuid;
            $padUuid  = $searchPad->uuid;
            $contractUuid   = $contract->uuid;
            $contractSerial = $contract->contract_serial;
            $creativeUuid = $creative->uuid;

            $transaction = Transaction::query()
                ->create([
                    'lead_id' => $lead->id,
                    'contact_id' => $lead->contact->id,
                    'company_id' => $lead->company->id,
                    'person_uuid' => $personUuid,
                    'contract_uuid' => $contractUuid,
                    'contract_serial' => $contractSerial,
                    'pad_uuid' => $padUuid,
                    'creative_uuid' => $creativeUuid,
                ]);

        } else {

            $contractUuid   = $transaction->contract_uuid;
            $contractSerial = $transaction->contract_serial;
            $creativeUuid   = $transaction->creative_uuid;
        }

        $invoice = $ordApi->invoice();

        $date = Carbon::parse($lead->cf('Дата рекламы')->getValue())->format('Y-m-d') ?? Carbon::now()->format('Y-m-d');
        $dateExpose = Carbon::parse($lead->cf('Дата выставления (акта)')->getValue())->format('Y-m-d') ?? Carbon::now()->format('Y-m-d');
        $dateStart = Carbon::parse($lead->cf('Дата рекламы факт')->getValue())->format('Y-m-d') ?? Carbon::now()->format('Y-m-d');
        $dateEnd = Carbon::parse($lead->cf('Дата окончания факт')->getValue())->format('Y-m-d') ?? Carbon::now()->format('Y-m-d');
        $dateEndPlan = Carbon::parse($lead->cf('Дата окончания план')->getValue())->format('Y-m-d') ?? Carbon::now()->format('Y-m-d');

        $invoice->uuid = Uuid::uuid4();
        $invoice->contract_external_id = $contractUuid;
        $invoice->date = $dateExpose;
        $invoice->date_start = $dateStart;
        $invoice->date_end = $dateEnd;
        $invoice->amount = $lead->sale;
        $invoice->client_role = 'agency';
        $invoice->contractor_role = 'publisher';
        $invoice->serial = $contractSerial;

        $invoice->creative_external_id = $creativeUuid;
        $invoice->pad_external_id = $padUuid;
        $invoice->date_start_planned = $dateStart;
        $invoice->date_end_planned = $dateEndPlan;
        $invoice->date_start_actual = $dateStart;
        $invoice->date_end_actual = $dateEnd;
        $invoice->amount_per_event = $lead->sale / $lead->cf('Количество показов')->getValue();
        $invoice->invoice_shows_count = $lead->cf('Количество показов')->getValue() * 1000;

        $invoice->pad_external_id = $padUuid;
        $invoice->creative_external_id = $creativeUuid;
        $invoice->date_start_planned = $date;
        $invoice->date_end_planned  = $dateEndPlan;
        $invoice->date_start_actual = $dateStart;
        $invoice->date_end_actual   = $dateEnd;
        $invoice->invoice_shows_count = $lead->cf('Количество показов')->getValue();
        $invoice->shows_count = $lead->cf('Количество показов')->getValue();
        $invoice->amount = $lead->sale;
        $invoice->amount_per_event = $invoice->amount / $invoice->shows_count;

        $invoice->create();

        $result = $invoice->add();//TODO куда крепить

        if (empty($result->error)) {

            $transaction->invoice_uuid = $invoice->uuid;
            $transaction->save();

            Notes::addOne($lead, 'Успешное создание акта : '.$transaction->invoice_uuid);

            $lead->cf('ОРД Акт')->setValue(json_encode($result, JSON_UNESCAPED_UNICODE));
            $lead->save();

        } else
            Notes::addOne($lead, 'Произошла ошибка при создании акта : '.$result ? json_encode($result->error, JSON_UNESCAPED_UNICODE) : 'Неизвестная ошибка');
    }
}
