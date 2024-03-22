<?php

namespace App\Console\Commands\Api\Ord;

use App\Models\Api\Ord\Contract;
use App\Models\Api\Ord\Pad;
use App\Services\Ord\OrdService;
use Illuminate\Console\Command;

class GetPads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ord:get-pads';

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
        $pads = (new OrdService('prod'))->pad()->list();

        if (count($pads) !== Pad::query()->count()) {

            foreach ($pads as $pad) {

                try {
                    if (!Pad::query()
                        ->whereUuid($pad)
                        ->exists()) {
                        $detail = (new OrdService('prod'))->pad()->get($pad);

                        Pad::query()->updateOrCreate(['uuid' => $pad], [
                            "create_date" => $detail->create_date,
                            "person_external_id" => $detail->person_external_id,
                            "is_owner" => $detail->is_owner,
                            "type" => $detail->type,
                            "name" => $detail->name,
                            "url" => $detail->url,
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
                }
            }
        }
    }
}
