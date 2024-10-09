<?php

namespace App\Console\Commands\Api\Sheets;

use App\Models\Api\Sheets\Directories\Link;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Check2Count extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check2-count {transaction}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $transaction = $this->argument('transaction');

        Http::post('https://h.albato.ru/wh/38/1lfh5q5/ymqv4-g3P2kzL58uu4SONJUsKo5jX-yuD5GHv5PPYCo/', [
            'name' => trim($transaction->name),
            'lead_id' => $transaction->lead_id,
        ]);
    }
}
