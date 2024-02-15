<?php

namespace App\Console\Commands\Api\Messages;

use App\Models\Account;
use App\Models\Message;
use App\Services\amoCRM\Client;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GetCreatedAtLead extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leads:created_at';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws \Exception
     */
    public function handle()
    {
        $amoApi = (new Client(account: Account::query()->first()))->init();

        $leadIds = Message::query()
            ->select('element_id')
            ->where('type', 'out')
            ->where('created_at', '>', Carbon::now()->subDay()->format('Y-m-d H:i:s'))
            ->where('lead_created_at', null)
            ->where('element_type', 'lead')
            ->groupBy('element_id')
            ->get();

        foreach ($leadIds as $leadId) {

            try {

                $createdAt = $amoApi->service->leads()->find($leadId->element_id)->created_at;

                Message::query()
                    ->where('element_type', 'lead')
                    ->where('element_id', $leadId->element_id)
                    ->where('lead_created_at', null)
                    ->update([
                        'lead_created_at'      => Carbon::parse($createdAt)->format('Y-m-d H:i:s'),
                        'lead_created_at_time' => Carbon::parse($createdAt)->format('H:i:s'),
                    ]);
            } catch (\Throwable $e) {}
        }

        return Command::SUCCESS;
    }
}
