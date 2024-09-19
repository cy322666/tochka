<?php

namespace App\Console\Commands\Api\Ord;

use App\Models\Api\Ord\Creative;
use App\Models\Api\Ord\Pad;
use App\Services\Ord\OrdService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GetCreative extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ord:get-creative';

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
        $creatives = (new OrdService(env('APP_ENV')))->creative()->list();

        if (count($creatives) !== Creative::query()->count()) {

            foreach ($creatives as $creative) {

                if (!$creative) break;

                try {
                    if (!Creative::query()
                        ->whereUuid($creative)
                        ->exists()) {

                        $detail = (new OrdService(env('APP_ENV')))->creative()->get($creative);

                        Creative::query()->updateOrCreate(['uuid' => $creative], [
                            'uuid' => $detail->external_id,
                            'name' => $detail->name,
                            'media' => json_encode($detail->media_external_ids),
                            'erid' => $detail->erid,
                            'contract_external_id' => $detail->contract_external_id,
                        ]);
                    }
                } catch (\Throwable $e) {
                    dump($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
                }
            }
        }
    }
}
