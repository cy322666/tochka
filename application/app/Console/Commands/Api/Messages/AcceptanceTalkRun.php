<?php

namespace App\Console\Commands\Api\Messages;

use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class AcceptanceTalkRun extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accept:run';

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
        $talks = Message::query()
            ->where('lead_created_at', '>', Carbon::now()->subDay()->format('Y-m-d H:i:s'))
            ->select('talk_id')
            ->distinct()
            ->get();

        foreach ($talks as $talk) {

            Artisan::call('accept:talk '.$talk->talk_id);
        }

        return Command::SUCCESS;
    }
}
