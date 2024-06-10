<?php

namespace App\Console\Commands\Api\Platform;

use App\Models\Platform\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SendFail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-fail ?{limit}';

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
        $orders = Order::query()->where('status', false);

        $orders = $this->argument('limit') ? $orders->limit($this->argument('limit'))->get() : $orders->get();

        foreach ($orders as $order) {

            sleep(1);

            \App\Jobs\Platform\SendOrder::dispatch($order);
        }
    }
}
