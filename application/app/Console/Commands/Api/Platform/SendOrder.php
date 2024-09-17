<?php

namespace App\Console\Commands\Api\Platform;

use Illuminate\Console\Command;

class SendOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'platform:send-order {order_id}';

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
        \App\Jobs\Platform\SendOrder::dispatch(\App\Models\Platform\Order::query()->find($this->argument('order_id')))->delay(3);
    }
}
