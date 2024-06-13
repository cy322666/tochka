<?php

namespace App\Console\Commands\Api\Platform;

use App\Models\Platform\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendFailCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-fail-cron';

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
        $orders = Order::query()
            ->where('status', false)
            ->where('created_at', '<', Carbon::now()->subMinute()->format('Y-m-d H:i:s'))
            ->limit(5)
            ->get();

        foreach ($orders as $order) {

            \App\Jobs\Platform\SendOrder::dispatch($order);
        }
    }
}
