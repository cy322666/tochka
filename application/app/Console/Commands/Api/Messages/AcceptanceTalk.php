<?php

namespace App\Console\Commands\Api\Messages;

use App\Models\Accept;
use App\Models\Account;
use App\Models\Message;
use App\Services\amoCRM\Client;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AcceptanceTalk extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accept:talk {talk_id}';

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
     */
    public function handle()
    {
        $amoApi = (new Client(account: Account::query()->first()))->init();

        $firstOut = Message::query()
            ->where('type', 'out')
            ->where('responsible_user_id', '!=', 0)
            ->where('talk_id', $this->argument('talk_id'))
//            ->whereBetween('msg_time_at', ['06:00:00', '18:00:00'])
//            ->whereBetween('lead_created_at_time', ['06:00:00', '18:00:00'])
            ->orderBy('msg_at')
            ->first();

        if (!$firstOut) return 1;

        $createdAt = $firstOut->lead_created_at;

        if ($createdAt == null) {

            $msg = Message::query()
                ->where('talk_id', $firstOut->talk_id)
                ->where('element_type', 'lead')
                ->first();

            if (!$msg) return 1;

            $lead = $amoApi->service->leads()->find($msg->element_id);

            $createdAt = $lead->created_at;

            $createdAt = Carbon::parse($createdAt)->format('Y-m-d H:i:s');
        }

        $diff = Carbon::parse($firstOut->msg_at)->diff(Carbon::parse($createdAt));

        $hours   = $diff->format('%h') * 60 * 60;
        $minutes = $diff->format('%i') * 60;
        $seconds = $diff->format('%s');

        try {

            Accept::query()->create([
                'time'      => $hours + $minutes + $seconds,
                'lead_id'   => $firstOut->element_id,
                'lead_created_at' => $createdAt,
                'first_out' => $firstOut->msg_at,
                'talk_id'   => $firstOut->talk_id,
                'time_at'   => Carbon::parse($createdAt)->format('H:i:s'),
                'responsible_user_id' => $firstOut->responsible_user_id == 0 ? $lead->responsible_user_id : $firstOut->responsible_user_id,
            ]);

        } catch (\Throwable $e) {

//            Log::error(__METHOD__, [$e->getMessage().' '.$e->getFile().' '.$e->getLine()]);
        }
        return 1;
    }
}
