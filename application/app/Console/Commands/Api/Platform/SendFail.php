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
    protected $signature = 'app:send-fail';

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
        $orders = Order::query()->where('status', false)->get();

        foreach ($orders as $order) {

            sleep(1);

            Artisan::call('platform:send-order', ['order_id' => $order->id]);
        }
    }
}
