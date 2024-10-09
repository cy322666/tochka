<?php

namespace App\Console\Commands\Api\Sheets;

use App\Models\Api\Sheets\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class Cron2Check extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cron2-check';

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
        $transactions = Transaction::query()
            ->where('status', false)
            ->where('check_1', true)
            ->where('check_2', false)
            ->get();

        foreach ($transactions as $transaction) {

            Artisan::call('app:check1-count', [
                'transaction' => $transaction
            ]);
        }
    }
}
