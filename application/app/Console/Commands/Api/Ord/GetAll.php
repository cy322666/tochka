<?php

namespace App\Console\Commands\Api\Ord;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class GetAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ord:get-all';

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
        //2 min
        Artisan::call('ord:get-persons');
        //5 min
        Artisan::call('ord:get-contracts');
        //5 min
        Artisan::call('ord:get-pads');
    }
}
