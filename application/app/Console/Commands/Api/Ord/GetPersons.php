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
        $persons = (new OrdService('prod'))->person()->list();

        if (count($persons) !== Person::query()->count()) {

            foreach ($persons as $person) {

                try {
                    if(!Person::query()
                        ->whereUuid($person)
                        ->exists()) {

                        $detail = (new OrdService('prod'))->person()->get($person);

                        Person::query()->create([
                            'uuid' => $person,
                            'juridical_details' => json_encode($detail->juridical_details) ?? null,
                            'create_date' => $detail->create_date,
                            'name'  => $detail->name ?? null,
                            'roles' => json_encode($detail->roles) ?? null,
                            'type'  => $detail->juridical_details->type ?? null,
                            'phone' => $detail->juridical_details->phone ?? null,
                            'inn'   => $detail->juridical_details->inn ?? null,
//                          'rs_url' => $detail->juridical_details->rs_url ?? null,
                        ]);
                    }
                } catch (\Throwable $e) {
                    dump($e->getMessage().' '.$e->getFile().' '.$e->getLine());
                }
            }
        }
    }
}
