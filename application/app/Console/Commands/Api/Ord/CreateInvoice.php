<?php

namespace App\Console\Commands\Api\Ord;

use App\Models\Account;
use App\Models\Api\Ord\Contract;
use App\Models\Api\Ord\Transaction;
use App\Services\amoCRM\Client;
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
    protected $signature = 'ord:create-invoice {transaction}';

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

        $ordApi = new OrdService(env('APP_ENV'));
        $amoApi = (new Client(
            Account::query()
                ->where('subdomain', 'tochkaznanij')
                ->first()
        ))->init();

        $lead    = $amoApi->service->leads()->find($transaction->lead_id);
        $invoice = $ordApi->invoice();

        $date = Carbon::parse($lead->cf('Дата рекламы')->getValue())->format('Y-m-d') ?? Carbon::now()->format('Y-m-d');
        $dateExpose = Carbon::parse($lead->cf('Дата выставления (акта)')->getValue())->format('Y-m-d') ?? Carbon::now()->format('Y-m-d');
        $dateStart = Carbon::parse($lead->cf('Дата рекламы факт')->getValue())->format('Y-m-d') ?? Carbon::now()->format('Y-m-d');
        $dateEnd = Carbon::parse($lead->cf('Дата окончания факт')->getValue())->format('Y-m-d') ?? Carbon::now()->format('Y-m-d');
        $dateEndPlan = Carbon::parse($lead->cf('Дата окончания план')->getValue())->format('Y-m-d') ?? Carbon::now()->format('Y-m-d');

        $invoice->uuid = Uuid::uuid4();
        $invoice->contract_external_id = $transaction->contract_uuid;
        $invoice->date = $dateExpose;
        $invoice->date_start = $dateStart;
        $invoice->date_end = $dateEnd;
        $invoice->amount = $lead->sale;
        $invoice->client_role = 'agency';
        $invoice->contractor_role = 'publisher';
        $invoice->serial = $transaction->contract_serial;

        $invoice->creative_external_id = $transaction->creative_uuid;
        $invoice->pad_external_id = $transaction->pad_uuid;
        $invoice->date_start_planned = $dateStart;
        $invoice->date_end_planned = $dateEndPlan;
        $invoice->date_start_actual = $dateStart;
        $invoice->date_end_actual = $dateEnd;
        $invoice->amount_per_event = $lead->sale / $lead->cf('Количество показов')->getValue();
        $invoice->invoice_shows_count = $lead->cf('Количество показов')->getValue() * 1000;

        $invoice->pad_external_id = $transaction->pad_uuid;
        $invoice->creative_external_id = $transaction->creative_uuid;
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

        } else
            throw new \Exception(json_encode($result->error));
    }
}
