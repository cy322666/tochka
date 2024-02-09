<?php

namespace App\Console\Commands\Api\Ord;

use App\Models\Api\Ord\Contract;
use App\Services\Ord\OrdService;
use Illuminate\Console\Command;

class GetContracts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ord:get-contracts';

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
        $contracts = (new OrdService())->contract()->list();

        foreach ($contracts as $contract) {

            Contract::query()->updateOrCreate(['uuid' => $contract]);
        }
    }
}
