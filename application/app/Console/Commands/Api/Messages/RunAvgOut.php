<?php

namespace App\Console\Commands\Api\Messages;

use App\Models\Message;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Command\Command as CommandAlias;

class RunAvgOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messages:run';

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
    public function handle(): int
    {
        $talks = Message::query()
            ->select('talk_id')
            ->distinct()
            ->get();

        foreach ($talks as $talk) {

            Artisan::call('messages:avg', ['talk_id' => $talk->talk_id]);
        }

        return CommandAlias::SUCCESS;
    }
}
