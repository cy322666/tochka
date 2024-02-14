<?php

namespace App\Console\Commands\Api\Ord;

use App\Models\Account;
use App\Services\amoCRM\Client;
use App\Services\Ord\OrdService;
use Illuminate\Console\Command;
use Ramsey\Uuid\Uuid;

class CreateInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ord:create-invoice';

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

        $lead    = $amoApi->service->leads()->find(44622459);
        $invoice = $ordApi->invoice();

        $invoice->uuid = Uuid::uuid4();
        $invoice->contract_external_id = '11724b84-2ac2-4f09-9494-f0316a5313de';
//        $invoice->date = ;
//        $invoice->serial = ;
//        $invoice->date_start = ;
//        $invoice->date_end = ;
//        $invoice->amount = ;
//        $invoice->client_role = ;
//        $invoice->contractor_role = ;
//        $invoice->create();
    }
}
