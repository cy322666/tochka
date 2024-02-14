<?php

namespace App\Console\Commands\Api\Ord;

use App\Models\Api\Ord\Person;
use App\Services\Ord\OrdService;
use Illuminate\Console\Command;

class GetPersons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ord:get-persons';

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
        $persons = (new OrdService())->person()->list();

        foreach ($persons as $person) {

            $detail = (new OrdService())->person()->get($person);

            Person::query()->updateOrCreate(['uuid' => $person], [
                'create_date' => $detail->create_date,
                'name'   => $detail->name ?? null,
                'roles'  => json_encode($detail->roles) ?? null,
                'juridical_details' => json_encode($detail->juridical_details) ?? null,
                'type'   => $detail->juridical_details->type ?? null,
                'phone'  => $detail->juridical_details->phone ?? null,
                'inn'    => $detail->juridical_details->inn ?? null,
                'rs_url' => $detail->juridical_details->rs_url ?? null,
            ]);
        }
    }
}
