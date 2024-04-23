<?php

namespace App\Console\Commands\Api\Ord;

use App\Models\Api\Ord\Contract;
use App\Models\Api\Ord\Person;
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
        $contracts = (new OrdService(env('APP_ENV')))->contract()->list();

        if (count($contracts) !== Contract::query()->count()) {

            foreach ($contracts as $contract) {

                try {
                    if(!Contract::query()
                        ->whereUuid($contract)
                        ->exists()) {

                        $detail = (new OrdService('prod'))->contract()->get($contract);

                        Contract::query()->create([
                            'uuid' => $contract,
                            'client_external_id' => $detail->client_external_id,
                            'contractor_external_id' => $detail->contractor_external_id,
                            'parent_contract_external_id' => $detail->parent_contract_external_id ?? null,
                            'create_date' => $detail->create_date,
                            'subject_type' => $detail->subject_type,
                            'type' => $detail->type,
                            'date' => $detail->date,
                            'serial' => $detail->serial,
                        ]);
                    }
                } catch (\Throwable $e) {
                    dump($e->getMessage().' '.$e->getFile().' '.$e->getLine());
                }
            }
        }
    }
}
