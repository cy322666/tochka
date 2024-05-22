<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Api\Ord\Transaction;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Leads;
use App\Services\Ord\OrdService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test {tran}';

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
        $transaction = Transaction::query()->find($this->argument('tran'));

        Artisan::call('ord:create-person',   ['transaction' => $transaction->id]);
        Artisan::call('ord:create-contract', ['transaction' => $transaction->id]);
        Artisan::call('ord:create-pad',      ['transaction' => $transaction->id]);
        Artisan::call('ord:create-creative', ['transaction' => $transaction->id]);
    }
}
